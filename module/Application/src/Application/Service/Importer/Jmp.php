<?php

namespace Application\Service\Importer;

use Application\Model\Answer;
use Application\Model\Filter;
use Application\Model\Part;
use Application\Model\Question\NumericQuestion;
use Application\Model\Questionnaire;
use Application\Model\Rule\Rule;
use Application\Model\Rule\FilterQuestionnaireUsage;
use Application\Model\Rule\FilterGeonameUsage;
use Application\Model\Rule\QuestionnaireUsage;
use Application\Model\Survey;
use Application\Model\Geoname;

class Jmp extends AbstractImporter
{

    private $defaultJustification = 'Imported from country files';
    private $partOffsets = array();
    private $cacheAlternateFilters = array();
    private $cacheFilterQuestionnaireUsages = array();
    private $cacheFormulas = array();
    private $importedQuestionnaires = array();
    private $colToParts = array();
    private $surveyCount = 0;
    private $questionnaireCount = 0;
    private $answerCount = 0;
    private $excludeCount = 0;
    private $questionnaireUsageCount = 0;
    private $ruleCount = 0;
    private $alternateFilterCount = 0;
    private $filterQuestionnaireUsageCount = 0;
    private $filterGeonameUsageCount = 0;

    /**
     * @var Rule
     */
    private $excludeRule;

    /**
     * @var Part
     */
    private $partUrban;

    /**
     * @var Part
     */
    private $partRural;

    /**
     * @var Part
     */
    private $partTotal;

    /**
     * Whether the currently imported country is considered developed
     * @var boolean
     */
    private $isDevelopedCountry;

    /**
     * Import data from file
     */
    public function import($filename)
    {
        $reader = \PHPExcel_IOFactory::createReaderForFile($filename);
        $reader->setReadDataOnly(true);

        $sheeNamesToImport = array_keys($this->definitions);
        $reader->setLoadSheetsOnly($sheeNamesToImport);
        $workbook = $reader->load($filename);

        $this->excludeRule = $this->getRule('Exclude from computing', '=NULL');
        $this->partUrban = $this->getEntityManager()->getRepository('Application\Model\Part')->getOrCreate('Urban');
        $this->partRural = $this->getEntityManager()->getRepository('Application\Model\Part')->getOrCreate('Rural');
        $this->partTotal = $this->getEntityManager()->getRepository('Application\Model\Part')->getOrCreate('Total');

        $this->partOffsets = array(
            3 => $this->partUrban,
            4 => $this->partRural,
            5 => $this->partTotal,
        );

        foreach ($sheeNamesToImport as $i => $sheetName) {

            $this->importOfficialFilters($this->definitions[$sheetName]);

            // Also create a filterSet with same name for the first filter
            $firstFilter = reset($this->cacheFilters);
            $filterSetRepository = $this->getEntityManager()->getRepository('Application\Model\FilterSet');
            $filterSet = $filterSetRepository->getOrCreate($firstFilter->getName());
            foreach ($firstFilter->getChildren() as $child) {
                if ($child->isOfficial()) {
                    $filterSet->addFilter($child);
                }
            }

            // Import high filter, but not their formula, we need them before importing QuestionnaireUsages
            $this->importHighFilters($this->definitions[$sheetName]['filterSet'], $this->definitions[$sheetName]['highFilters']);

            $workbook->setActiveSheetIndex($i);
            $sheet = $workbook->getSheet($i);

            // Try to import the first 50 questionnaires if data found
            // According to tab GraphData_W, maximum should be 40, but better safe than sorry
            $this->colToParts = array();
            for ($col = 0; $col < 50 * 6; $col += 6) {
                $this->importQuestionnaire($sheet, $col);
            }

            // Second pass on imported questionnaires to process cross-questionnaire things
            echo 'Importing Calculations, Estimates and Ratios';
            foreach ($this->importedQuestionnaires as $col => $questionnaire) {
                $this->importQuestionnaireUsages($sheet, $col, $questionnaire);
                echo '.';
            }
            echo PHP_EOL;

            // Third pass to import formulas for high filters
            $this->finishHighFilters($this->definitions[$sheetName]['highFilters'], $sheet);

            // Fourth pass to hardcode special cases of formulas
            echo 'Finishing special cases of Ratios';
            $this->finishRatios();
            echo PHP_EOL;
        }

        $this->getEntityManager()->flush();

        $answerRepository = $this->getEntityManager()->getRepository('Application\Model\Answer');
        $answerRepository->updateAbsoluteValueFromPercentageValue();

        return <<<STRING

Surveys          : $this->surveyCount
Questionnaires   : $this->questionnaireCount
Alternate Filters: $this->alternateFilterCount
Answers          : $this->answerCount
Rules            : $this->ruleCount
Uses of Exclude  : $this->excludeCount
Uses of Rule for Filter       : $this->filterQuestionnaireUsageCount
Uses of Rule for Filter       : $this->filterGeonameUsageCount
Uses of Rule for Questionnaire: $this->questionnaireUsageCount

STRING;
    }

    /**
     * Standardize Survey code
     * @param string $code
     * @param string $year
     */
    public function standardizeSurveyCode($code, $year)
    {
        // Sometimes code use 4-digit year instead of 2-digit, we replace it here
        // so we can find existing survey. This is the case of Burkina Faso, survey ENA2010
        $twoDigitsYear = substr($year, -2);
        $code = str_replace($year, $twoDigitsYear, $code);

        $codeMapping = array(
            'ENHOGAR09-10' => 'ENHGR09-10', // Dominican Republic
            'CEN00-02' => 'CEN02', // French Polynesia
            'EMP10' => 'EPM10', // Madagascar
            'CEN2006/08' => 'CEN06/08', // Martinique
            'Census' => 'CEN09', // New Caledonia
            'CEN' => 'CEN99', //
        );

        // Replace special cases
        $code = trim($code);
        if (isset($codeMapping[$code])) {
            $code = $codeMapping[$code];
        }

        return $code;
    }

    /**
     * Import a questionnaire from the given column offset.
     * Questionnaire and Answers will always be created new. All other objects will be retrieved from database if available.
     * @param \PHPExcel_Worksheet $sheet
     * @param integer $col
     * @return void
     */
    protected function importQuestionnaire(\PHPExcel_Worksheet $sheet, $col)
    {
        $code = trim($sheet->getCellByColumnAndRow($col + 2, 1)->getCalculatedValue());

        // If no code found, we assume no survey at all
        if (!$code) {
            return;
        }

        $year = $sheet->getCellByColumnAndRow($col + 3, 3)->getCalculatedValue();
        $code = $this->standardizeSurveyCode($code, $year);

        // Load or create survey
        $surveyRepository = $this->getEntityManager()->getRepository('Application\Model\Survey');
        $survey = $surveyRepository->findOneBy(array('code' => $code));
        if (!$survey) {
            $survey = new Survey();

            $survey->setIsActive(true);
            $survey->setCode($code);
            $survey->setName($sheet->getCellByColumnAndRow($col + 0, 2)->getCalculatedValue());
            $survey->setYear($year);

            if (!$survey->getName()) {
                $survey->setName($survey->getCode());
            }

            if (!$survey->getYear()) {
                echo 'WARNING: skipped survey because there is no year. On sheet "' . $sheet->getTitle() . '" cell ' . $sheet->getCellByColumnAndRow($col + 3, 3)->getCoordinate() . PHP_EOL;

                return;
            }
            $this->getEntityManager()->persist($survey);
            $this->getEntityManager()->flush();
            $this->surveyCount++;
        }

        // Create questionnaire
        $countryCell = $sheet->getCellByColumnAndRow($col + 3, 1);
        $questionnaire = $this->getQuestionnaire($sheet, $col, $survey, $countryCell);
        if (!$questionnaire) {
            echo 'WARNING: skipped questionnaire because there is no country name. On sheet "' . $sheet->getTitle() . '" cell ' . $countryCell->getCoordinate() . PHP_EOL;

            return;
        }

        echo 'Survey: ' . $survey->getCode() . PHP_EOL;
        echo 'Country: ' . $questionnaire->getGeoname()->getName() . PHP_EOL;

        $this->importAnswers($sheet, $col, $survey, $questionnaire);

        $this->getEntityManager()->flush();

        // Keep a trace of what column correspond to what questionnaire for second pass
        $this->importedQuestionnaires[$col] = $questionnaire;
        foreach ($this->partOffsets as $offset => $part) {
            $this->colToParts[$col + $offset]['questionnaire'] = $questionnaire;
            $this->colToParts[$col + $offset]['part'] = $part;
        }
    }

    /**
     * Import all answers found at given column offset.
     * Questions will only be created if an answer exists.
     * @param \PHPExcel_Worksheet $sheet
     * @param integer $col
     * @param Questionnaire $questionnaire
     * @return integer imported answer count
     */
    protected function importAnswers(\PHPExcel_Worksheet $sheet, $col, Survey $survey, Questionnaire $questionnaire)
    {
        $knownRows = array_keys($this->cacheFilters);
        array_shift($knownRows); // Skip first filter, since it's not an actual row, but the sheet topic (eg: "Access to drinking water sources")
        // Remove negative rows which were replacement filters
        $knownRows = array_filter($knownRows, function($row) {
            return $row > 0 && $row < 100;
        });

        $answerCount = 0;
        foreach ($knownRows as $row) {

            $filter = $this->cacheFilters[$row];

            // Use alternate instead of official, if any
            $alternateFilterName = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
            if ($alternateFilterName) {
                $filter = $this->getAlternateFilter($questionnaire, $alternateFilterName, $filter);
            }

            // Import answers for each parts
            $question = null;
            foreach ($this->partOffsets as $offset => $part) {
                $answerCell = $sheet->getCellByColumnAndRow($col + $offset, $row);

                // Only import value which are numeric, and NOT formula,
                // unless an alternate filter is defined, in this case we will import the formula result
                if ($alternateFilterName || $answerCell->getDataType() == \PHPExcel_Cell_DataType::TYPE_NUMERIC) {

                    // If there is actually no value, skip it (need to be done after previous IF to avoid formula exception within PHPExcel)
                    $value = $this->getCalculatedValueSafely($answerCell);
                    if (!is_numeric($value) || ($value == 0 && $answerCell->getDataType() == \PHPExcel_Cell_DataType::TYPE_FORMULA)) {
                        continue;
                    }

                    if (!$question) {
                        $question = $this->getQuestion($survey, $filter);
                    }

                    $answer = new Answer();
                    $this->getEntityManager()->persist($answer);
                    $answer->setQuestionnaire($questionnaire);
                    $answer->setQuestion($question);
                    $answer->setPart($part);
                    $answer->setValuePercent($value / 100);

                    $answerCount++;
                }
            }
        }

        $this->answerCount += $answerCount;
        echo "Answers: " . $answerCount . PHP_EOL . PHP_EOL;
    }

    /**
     * Get or create a questionnaire.
     *
     * It will only get a questionnaire from previous tab, so only if we are in Sanitation.
     * In all other cases a new questionnaire will be created.
     *
     * @param \PHPExcel_Worksheet $sheet
     * @param integer $col
     * @param \Application\Model\Survey $survey
     * @param \PHPExcel_Cell $countryCell
     * @return null|\Application\Model\Questionnaire
     * @throws \Exception
     */
    protected function getQuestionnaire(\PHPExcel_Worksheet $sheet, $col, Survey $survey, \PHPExcel_Cell $countryCell)
    {
        // If we already imported a questionnaire on that column with same Survey,
        // returns it directly. That means we are on second tab (Sanitation) and we
        // assume the questionnaire is the same as the one we found on first tab (Water)
        $otherQuestionnaire = @$this->importedQuestionnaires[$col];
        if ($otherQuestionnaire && $otherQuestionnaire->getSurvey() === $survey) {
            return $otherQuestionnaire;
        }

        // Mapping JMP country names to Geoname country names
        $countryNameMapping = array(
            'Occupied Palestinian Territory' => 'West Bank and Gaza Strip',
            'Palestine' => 'West Bank and Gaza Strip',
            'Palestinian Territory' => 'West Bank and Gaza Strip',
            'Republic of Korea' => 'Republic of Korea',
            'St. Vincent and Grenadines' => 'Saint Vincent and the Grenadines',
            'St Vincent & grenadines' => 'Saint Vincent and the Grenadines',
            'State of Palestine' => 'West Bank and Gaza Strip',
            'Libyan Arab Jamahiriya' => 'Libyan Arab Jamahiriya',
            'Bolivia' => 'Bolivia (Plurinational State of)',
            'Cape Verde' => 'Cabo Verde',
            'Micronesia' => 'Micronesia (Fed. States of)',
            'Reunion' => 'Réunion',
            'Tanzania' => 'United Republic of Tanzania',
            // Unusual spelling
            'Antigua & Barbuda' => 'Antigua and Barbuda',
            'Afganistan' => 'Afghanistan',
            'Dominican Rep' => 'Dominican Republic',
            'Guinée' => 'Guinea',
            'Senagal' => 'Senegal',
            'Cap Verde' => 'Cabo Verde',
            'Congo DR' => 'Democratic Republic of the Congo',
            'Bosnia' => 'Bosnia and Herzegovina',
            // Case mistake
            'ANGOLA' => 'Angola',
            'BURUNDI' => 'Burundi',
            'ETHIOPIA' => 'Ethiopia',
            'GAMBIA' => 'Gambia',
            'GUAM' => 'Guam',
            'INDONESIA' => 'Indonesia',
            'NIGERIA' => 'Nigeria',
            'RWANDA' => 'Rwanda',
            'Saint lucia' => 'Saint Lucia',
            'SOUTH AFRICA' => 'South Africa',
            'ZIMBABWE' => 'Zimbabwe',
        );

        $developedCountries = array(
            'ALB',
            'AND',
            'AUS',
            'AUT',
            'BEL',
            'BGR',
            'BIH',
            'BLR',
            'BMU',
            'CAN',
            'CHE',
            'CIS',
            'CYP',
            'CZE',
            'DEU',
            'DNK',
            'ESP',
            'EST',
            'FIN',
            'FRA',
            'FRO',
            'GBR',
            'GRC',
            'GRL',
            'HRV',
            'HUN',
            'IMN',
            'IRL',
            'ISL',
            'ISR',
            'ITA',
            'JPN',
            'LIE',
            'LTU',
            'LUX',
            'LVA',
            'MCO',
            'MDA',
            'MKD',
            'MLT',
            'MNE',
            'NLD',
            'NOR',
            'NZL',
            'POL',
            'PRT',
            'ROU',
            'RUS',
            'SMR',
            'SRB',
            'SVK',
            'SVN',
            'SWE',
            'UKR',
            'USA',
        );

        // Some files have a buggy self-referencing formula, so we need to fallback on cached result of formula
        $countryName = $this->getCalculatedValueSafely($countryCell);

        // Apply mapping if any
        $countryName = trim(@$countryNameMapping[$countryName] ? : $countryName);

        // Skip questionnaire if there is no country name
        if (!$countryName) {
            return null;
        }


        $countryRepository = $this->getEntityManager()->getRepository('Application\Model\Country');
        $country = $countryRepository->findOneBy(array('name' => $countryName));
        if (!$country) {
            throw new \Exception('No country found for name "' . $countryName . '"');
        }
        $this->isDevelopedCountry = in_array($country->getIso3(), $developedCountries);
        $geoname = $country->getGeoname();

        $questionnaire = new Questionnaire();
        $questionnaire->setSurvey($survey);
        $questionnaire->setDateObservationStart(new \DateTime($survey->getYear() . '-01-01'));
        $questionnaire->setDateObservationEnd(new \DateTime($survey->getYear() . '-12-31T23:59:59'));
        $questionnaire->setGeoname($geoname);
        $questionnaire->setComments($sheet->getCellByColumnAndRow($col + 0, 3)->getCalculatedValue());

        $this->getEntityManager()->persist($questionnaire);
        $this->getEntityManager()->flush();
        $this->questionnaireCount++;

        return $questionnaire;
    }

    /**
     * Some files have a buggy self-referencing formula, so we need to fallback on cached result of formula
     * @param \PHPExcel_Cell $cell
     * @return type
     * @throws \PHPExcel_Exception
     */
    protected function getCalculatedValueSafely(\PHPExcel_Cell $cell)
    {
        try {
            return $cell->getCalculatedValue();
        } catch (\PHPExcel_Exception $exception) {

            // Fallback on cached result
            if (preg_match('/(Cyclic Reference in Formula|Formula Error)/', $exception->getMessage())) {
                $value = $cell->getOldCalculatedValue();

                return $value == \PHPExcel_Calculation_Functions::NA() ? null : $value;
            } else {
                // Forward exception
                throw $exception;
            }
        }
    }

    /**
     * Returns an alternate filter linked to the official either from memory cache, or newly created
     * @param Questionnaire $questionnaire
     * @param string $name
     * @param Filter $officialFilter
     * @return Filter
     */
    protected function getAlternateFilter(Questionnaire $questionnaire, $name, Filter $officialFilter)
    {
        if ($name == $officialFilter->getName()) {
            return $officialFilter;
        }

        $key = \Application\Utility::getCacheKey(func_get_args());
        if (array_key_exists($key, $this->cacheAlternateFilters)) {
            $filter = $this->cacheAlternateFilters[$key];
        } else {
            $filter = new Filter();
            $this->getEntityManager()->persist($filter);
            $filter->setName($name);
            $filter->setOfficialFilter($officialFilter);
            $filter->setQuestionnaire($questionnaire);
            $this->alternateFilterCount++;
        }

        $this->cacheAlternateFilters[$key] = $filter;

        return $filter;
    }

    /**
     * Returns a question either from database, or newly created
     * @param Questionnaire $survey
     * @param Filter $filter
     * @return NumericQuestion
     */
    protected function getQuestion(Survey $survey, Filter $filter)
    {
        $questionRepository = $this->getEntityManager()->getRepository('Application\Model\Question\NumericQuestion');

        $key = \Application\Utility::getCacheKey(func_get_args());

        if (array_key_exists($key, $this->cacheQuestions)) {
            $question = $this->cacheQuestions[$key];
        } else {
            $this->getEntityManager()->flush();
            $question = $questionRepository->findOneBy(array('survey' => $survey, 'filter' => $filter));
        }

        if (!$question) {
            $question = new NumericQuestion();
            $this->getEntityManager()->persist($question);

            $question->setSurvey($survey);
            $question->setFilter($filter);
            $question->setSorting($survey->getQuestions()->count());
            $question->setName($filter->getName());
            $question->setParts(new \Doctrine\Common\Collections\ArrayCollection(array($this->partRural, $this->partUrban, $this->partTotal)));
            $question->setIsPopulation(true);
            $this->getEntityManager()->persist($question);
        }

        $this->cacheQuestions[$key] = $question;

        return $question;
    }

    /**
     * Import all rules on the questionnaire level (Calculations, Estimates and Ratios)
     * @param \PHPExcel_Worksheet $sheet
     * @param integer $col
     * @param Questionnaire $questionnaire
     */
    protected function importQuestionnaireUsages(\PHPExcel_Worksheet $sheet, $col, Questionnaire $questionnaire)
    {
        foreach ($this->definitions[$sheet->getTitle()]['questionnaireUsages'] as $group) {
            foreach ($group as $row) {
                foreach ($this->partOffsets as $offset => $part) {
                    $this->getQuestionnaireUsage($sheet, $col, $row, $offset, $questionnaire, $part);
                }
            }
        }
    }

    /**
     * Create or get a QuestionnaireUsage and its Formula
     * @param \PHPExcel_Worksheet $sheet
     * @param integer $col
     * @param integer $row
     * @param integer $offset
     * @param Questionnaire $questionnaire
     * @param Part $part
     * @return QuestionnaireUsage|null
     */
    protected function getQuestionnaireUsage(\PHPExcel_Worksheet $sheet, $col, $row, $offset, Questionnaire $questionnaire, Part $part)
    {
        $name = $this->getCalculatedValueSafely($sheet->getCellByColumnAndRow($col + 1, $row));
        $value = $this->getCalculatedValueSafely($sheet->getCellByColumnAndRow($col + $offset, $row));

        if ($name && !is_null($value) || $value <> 0) {

            $rule = $this->getRuleFromCell($sheet, $col, $row, $offset, $questionnaire, $part);

            // If formula was non-existing, or invalid, cannot do anything more
            if (!$rule) {
                return null;
            }

            // If we had an existing formula, maybe we also have an existing association
            $assoc = $questionnaire->getQuestionnaireUsages()->filter(function($usage) use ($questionnaire, $part, $rule) {
                        return $usage->getQuestionnaire() === $questionnaire && $usage->getPart() === $part && $usage->getRule() === $rule;
                    })->first();

            // If association doesn't exist yet, create it
            if (!$assoc) {
                $assoc = new QuestionnaireUsage();
                $assoc->setJustification($this->defaultJustification)
                        ->setQuestionnaire($questionnaire)
                        ->setRule($rule)
                        ->setPart($part);

                $this->getEntityManager()->persist($assoc);
                $this->questionnaireUsageCount++;
            }

            return $assoc;
        }

        return null;
    }

    /**
     * Create or get a formula by converting Excel syntax to our own syntax
     * @param \PHPExcel_Worksheet $sheet
     * @param integer $col
     * @param integer $row
     * @param integer $offset
     * @param Questionnaire $questionnaire
     * @param Part $part
     * @param string $forcedName
     * @return null|Rule
     */
    protected function getRuleFromCell(\PHPExcel_Worksheet $sheet, $col, $row, $offset, Questionnaire $questionnaire, Part $part, $forcedName = null)
    {
        $cell = $sheet->getCellByColumnAndRow($col + $offset, $row);
        $originalFormula = $cell->getValue();

        // if we have nothing at all, cannot do anything
        if (is_null($originalFormula) || $originalFormula == '') {
            return null;
            // if the formula is actually not a formula, transform into formula
        } elseif (@$originalFormula[0] != '=') {
            $originalFormula = '=' . $originalFormula;
        }

        // Excel files sometimes express percent as 0 - 100, and sometimes as 0.00 - 1.00,
        // whereas in GIMS it always is 0.00 - 1.00. So this means we have to drop the
        // useless conversion done in formula, but only if there is at least one cell reference
        // in the formula ("=15/100" should be kept intact, as seen in Afghanistan, Table_S, AZ100)
        $cellPattern = '\$?(([[:alpha:]]+)\$?(\\d+))';
        if (preg_match("/$cellPattern/", $originalFormula))
            $replacedFormula = str_replace(array('*100', '/100'), '', $originalFormula);
        else
            $replacedFormula = $originalFormula;

        // For the same reason, we need to replace complementary computing based on 100, to be based on 1
        // eg: "=100-A23" => "=1-A23"
        $replacedFormula = preg_replace('/([^a-zA-Z])100-/', '${1}1-', $replacedFormula);

        // Some formulas, Estimations and Calculation, hardcode values as percent between 0 - 100,
        // we need to convert them to 0.00 - 1.00
        $ruleRows = $this->definitions[$sheet->getTitle()]['questionnaireUsages'];
        if (in_array($row, $ruleRows['Estimate']) || in_array($row, $ruleRows['Calculation'])) {

            // Convert very simple formula with numbers only and -/+ operations. Anything more complex
            // would be too dangerous. This is the case for Cambodge DHS05 "Bottled water with HC" estimation, or
            // Solomon Islands, Tables_W!AH88
            // eg: "=29.6" => "=0.296", "=29.6+10" => "=0.396"
            $replacedFormula = \Application\Utility::pregReplaceUniqueCallback('/^=[-+\.\d ]+$/', function($matches) {
                        $number = \PHPExcel_Calculation::getInstance()->_calculateFormulaValue($matches[0]);
                        $number = $number / 100;

                        return "=$number";
                    }, $replacedFormula);

            // Convert when using a Ratio, this is the case of Thailand, Tables_W!CD88
            // eg: "=44.6*BR102" => "=0.446*BR102"
            $replacedFormula = \Application\Utility::pregReplaceUniqueCallback("/^=([-+\.\d ]+)(\*$cellPattern)$/", function($matches) use($ruleRows) {
                        $number = $matches[1];

                        if (in_array($matches[5], $ruleRows['Ratio'])) {
                            $number = $number / 100;
                        }

                        return "=$number" . $matches[2];
                    }, $replacedFormula);
        }

        // Expand range syntax to enumerate each cell: "A1:A5" => "{A1,A2,A3,A4,A5}"
        $expandedFormula = \Application\Utility::pregReplaceUniqueCallback("/$cellPattern:$cellPattern/", function($matches) use ($sheet, $cell) {

                    // This only expand vertical ranges, not horizontal ranges (which probably never make any sense for JMP anyway)
                    if ($matches[2] != $matches[5]) {
                        throw new \Exception('Horizontal range are not supported: ' . $matches[0] . ' found in ' . $sheet->getTitle() . ', cell ' . $cell->getCoordinate());
                    }

                    $expanded = array();
                    for ($i = $matches[3]; $i <= $matches[6]; $i++) {
                        $expanded[] = $matches[2] . $i;
                    }

                    return '{' . join(',', $expanded) . '}';
                }, $replacedFormula);

        // Replace special cell reference with some hardcoded formulas
        $expandedFormula = \Application\Utility::pregReplaceUniqueCallback("/$cellPattern/", function($matches) use ($sheet, $cell) {

                    $replacement = @$this->definitions[$sheet->getTitle()]['cellReplacements'][$matches[3]];
                    if ($replacement) {
                        // Use same column for replacement as the original cell reference
                        $replacement = str_replace('A', $matches[2], $replacement);

                        return '(' . $replacement . ')';
                    } else {
                        return $matches[0];
                    }
                }, $expandedFormula);

        // Replace all cell reference with our own syntax
        $convertedFormula = \Application\Utility::pregReplaceUniqueCallback("/$cellPattern/", function($matches) use ($sheet, $questionnaire, $part, $expandedFormula) {
                    $refCol = \PHPExcel_Cell::columnIndexFromString($matches[2]) - 1;
                    $refRow = $matches[3];

                    // We first look for filter on negative indexes to find original filters in case we made
                    // replacements (for Sanitation), because formulas always refers to the originals,
                    // not the one we created in this script
                    $refFilter = @$this->cacheFilters[-$refRow] ? : @$this->cacheFilters[$refRow];

                    // If couldn't find filter yet, try last chance in high filters
                    if (!$refFilter) {
                        foreach ($this->definitions[$sheet->getTitle()]['highFilters'] as $highFilterName => $highFilterData) {
                            if ($refRow == $highFilterData['row']) {
                                $refFilter = $this->cacheHighFilters[$highFilterName];
                            }
                        }
                    }

                    $refFilterId = $refFilter ? $refFilter->getId() : null;

                    if (isset($this->importedQuestionnaires[$refCol])) {
                        $refQuestionnaireId = $this->importedQuestionnaires[$refCol]->getId();

                        return "{F#$refFilterId,Q#$refQuestionnaireId}";
                    }

                    // Find out referenced Questionnaire
                    $refData = @$this->colToParts[$refCol];

                    // If reference an non-existing questionnaire, replace reference with NULL
                    if (!$refData) {
                        return 'NULL';
                    }

                    $refQuestionnaire = $refData['questionnaire'];
                    if ($refQuestionnaire === $questionnaire)
                        $refQuestionnaireId = 'current';
                    else
                        $refQuestionnaireId = $refQuestionnaire->getId();

                    // Find out referenced Part
                    $refPart = $refData['part'];
                    if ($refPart === $part)
                        $refPartId = 'current';
                    else
                        $refPartId = $refPart->getId();


                    // Simple case is when we reference a filter
                    if ($refFilterId) {
                        return "{F#$refFilterId,Q#$refQuestionnaireId,P#$refPartId}";
                        // More advanced case is when we reference another QuestionnaireUsage (Calculation, Estimate or Ratio)
                    } else {

                        // Find the column of the referenced questionnaire
                        $refColQuestionnaire = array_search($refQuestionnaire, $this->importedQuestionnaires);

                        $refQuestionnaireUsage = $this->getQuestionnaireUsage($sheet, $refColQuestionnaire, $refRow, $refCol - $refColQuestionnaire, $refQuestionnaire, $refPart);

                        if ($refQuestionnaireUsage) {

                            // If not ID yet, we need to save to DB to have valid ID
                            if (!$refQuestionnaireUsage->getRule()->getId()) {
                                $this->getEntityManager()->flush();
                            }

                            $refRuleId = $refQuestionnaireUsage->getRule()->getId();

                            return "{R#$refRuleId,Q#$refQuestionnaireId,P#$refPartId}";
                        } else {
                            return 'NULL'; // if no formula found at all, return NULL string which will behave like an empty cell in PHPExcell
                        }
                    }
                }, $expandedFormula);

        // In some case ISTEXT() is used to check if a number does not exist. But GIMS
        // always returns either a number or NULL, never empty string. So we adapt formula
        // to use a more semantic way to check absence of number
        $isTextUsageWhichCannotBeText = array(
            '/ISTEXT\((\{F#(\d+|current),Q#(\d+|current),P#(\d+|current)\})\)/',
            '/ISTEXT\((\{R#(\d+),Q#(\d+|current),P#(\d+|current)\})\)/',
            '/ISTEXT\((\{Q#(\d+|current),P#(\d+|current)\})\)/',
            '/ISTEXT\((\{F#(\d+|current)})\)/',
        );
        $convertedFormula = preg_replace($isTextUsageWhichCannotBeText, 'NOT(ISNUMBER($1))', $convertedFormula);

        $prefix = '';
        foreach ($this->definitions[$sheet->getTitle()]['questionnaireUsages'] as $label => $rows) {
            if (in_array($row, $rows)) {
                $prefix = $label . ': ';
            }
        }

        $name = $forcedName ? : $this->getCalculatedValueSafely($sheet->getCellByColumnAndRow($col + 1, $row));

        // Some countries have estimates with non-zero values but without name! (Yemen, Tables_W, DHS92, estimates line 88)
        if (!$name) {
            $name = 'Unnamed formula imported from country files';
        }

        return $this->getRule($prefix . $name, $convertedFormula);
    }

    /**
     * Create or get a formula
     * @param string $name
     * @param string $formula
     * @return Rule
     */
    protected function getRule($name, $formula)
    {
        $key = \Application\Utility::getCacheKey(array($formula));
        if (array_key_exists($key, $this->cacheFormulas)) {
            return $this->cacheFormulas[$key];
        }

        $ruleRepository = $this->getEntityManager()->getRepository('Application\Model\Rule\Rule');

        // Look for existing formula (to prevent duplication)
        $this->getEntityManager()->flush();
        $rule = $ruleRepository->findOneByFormula($formula);

        if (!$rule) {
            $rule = new Rule();
            $rule->setName($name)
                    ->setFormula($formula);
            $this->getEntityManager()->persist($rule);
            $this->ruleCount++;
        }

        $this->cacheFormulas[$key] = $rule;

        return $rule;
    }

    /**
     * Finish high filters, by importing their exclude rules and their formulas (if any)
     * @param array $filters
     * @param \PHPExcel_Worksheet $sheet
     */
    protected function finishHighFilters(array $filters, \PHPExcel_Worksheet $sheet)
    {
        $complementaryTotalFormula = $this->getRule('Total part is sum of parts if both are available', '=IF(AND(ISNUMBER({F#current,Q#current,P#' . $this->partRural->getId() . ',L#2}), ISNUMBER({F#current,Q#current,P#' . $this->partUrban->getId() . ',L#2})), ({F#current,Q#current,P#' . $this->partRural->getId() . ',L#2} * {Q#current,P#' . $this->partRural->getId() . '} + {F#current,Q#current,P#' . $this->partUrban->getId() . ',L#2} * {Q#current,P#' . $this->partUrban->getId() . '}) / {Q#current,P#' . $this->partTotal->getId() . '}, {self})');
        // Get or create all filter
        echo 'Finishing high filters';
        foreach ($filters as $filterName => $filterData) {

            $highFilter = $this->cacheHighFilters[$filterName];

            // Import high filters' formulas
            foreach ($this->importedQuestionnaires as $col => $questionnaire) {

                foreach ($this->partOffsets as $offset => $part) {

                    // If we are total part, we first add the complementory formula which is hardcoded
                    // and can be found in tab "GraphData_W". This formula defines the total as the sum of its parts
                    if ($part === $this->partTotal) {
                        $this->getFilterQuestionnaireUsage($highFilter, $questionnaire, $complementaryTotalFormula, $part, true);
                    }

                    // If the high filter exists in "Tables_W", then it may have a special formula that need to be imported
                    if ($filterData['row']) {
                        $rule = $this->getRuleFromCell($sheet, $col, $filterData['row'], $offset, $questionnaire, $part, $filterName . ' (' . $questionnaire->getName() . ($part ? ', ' . $part->getName() : '') . ')');
                        if ($rule) {
                            $this->getFilterQuestionnaireUsage($highFilter, $questionnaire, $rule, $part);
                        }
                    }
                }

                $this->importExcludes($sheet, $col, $questionnaire, $highFilter, $filterData);
            }
            echo '.';
        }

        $this->importHighFilterGeonameUsages($filters);

        echo PHP_EOL;
    }

    /**
     * Create or get a filterQuestionnaireUsage
     * @param Filter $filter
     * @param Questionnaire $questionnaire
     * @param Rule $rule
     * @param Part $part
     * @param boolean $isSecondLevel
     * @return FilterQuestionnaireUsage
     */
    protected function getFilterQuestionnaireUsage(Filter $filter, Questionnaire $questionnaire, Rule $rule, Part $part, $isSecondLevel = false)
    {
        $key = \Application\Utility::getCacheKey(func_get_args());
        if (array_key_exists($key, $this->cacheFilterQuestionnaireUsages)) {
            $filterQuestionnaireUsage = $this->cacheFilterQuestionnaireUsages[$key];
        } else {
            $filterQuestionnaireUsage = new FilterQuestionnaireUsage();
            $filterQuestionnaireUsage->setJustification($this->defaultJustification)
                    ->setQuestionnaire($questionnaire)
                    ->setRule($rule)
                    ->setPart($part)
                    ->setFilter($filter)
                    ->setIsSecondLevel($isSecondLevel);

            $this->getEntityManager()->persist($filterQuestionnaireUsage);

            if ($rule === $this->excludeRule) {
                $this->excludeCount++;
            } else {
                $this->filterQuestionnaireUsageCount++;
            }
        }

        return $filterQuestionnaireUsage;
    }

    /**
     * Create or get a FilterGeonameUsage
     * @param Filter $filter
     * @param Geoname $geoname
     * @param Rule $rule
     * @param Part $part
     * @return FilterGeonameUsage
     */
    protected function getFilterGeonameUsage(Filter $filter, Geoname $geoname, Rule $rule, Part $part)
    {
        $repository = $this->getEntityManager()->getRepository('Application\Model\Rule\FilterGeonameUsage');

        $this->getEntityManager()->flush();
        $filterGeonameUsage = $repository->findOneBy(array(
            'rule' => $rule,
            'part' => $part,
            'filter' => $filter,
            'geoname' => $geoname,
        ));

        if (!$filterGeonameUsage) {
            $filterGeonameUsage = new FilterGeonameUsage();
            $filterGeonameUsage->setJustification($this->defaultJustification)
                    ->setRule($rule)
                    ->setPart($part)
                    ->setFilter($filter)
                    ->setGeoname($geoname);

            $this->getEntityManager()->persist($filterGeonameUsage);
            $this->filterGeonameUsageCount++;
        }

        return $filterGeonameUsage;
    }

    /**
     * Import FilterGeonameUsage for all highfilters
     * @param array $filters
     */
    public function importHighFilterGeonameUsages(array $filters)
    {
        foreach ($this->importedQuestionnaires as $questionnaire) {
            foreach ($filters as $filterName => $filterData) {
                $highFilter = $this->cacheHighFilters[$filterName];

                foreach ($filterData['formulas'] as $formulaData) {


                    $part = $this->partOffsets[$formulaData[0]];
                    $formula = $formulaData[1];
                    $isDevelopedFormula = @$formulaData[2];

                    // Only add developed formulas for developed countries
                    if ($isDevelopedFormula && !$this->isDevelopedCountry) {
                        continue;
                    }

                    // Replace our filter name with actual ID in formulas
                    foreach ($filters as $filterNameOther => $foo) {
                        $otherHighFilter = $this->cacheHighFilters[$filterNameOther];
                        $id = $otherHighFilter->getId();
                        $formula = str_replace('COUNT({' . $filterNameOther, "COUNT({F#$id", $formula);
                        $formula = str_replace('AVERAGE({' . $filterNameOther, "AVERAGE({F#$id", $formula);
                        $formula = str_replace($filterNameOther . 'LATER', "{F#$id,+1}", $formula);
                        $formula = str_replace($filterNameOther, "{F#$id}", $formula);
                    }

                    $suffix = $isDevelopedFormula ? ' (for developed countries)' : '';
                    $rule = $this->getRule('Regression: ' . $highFilter->getName() . $suffix, $formula);
                    $this->getFilterGeonameUsage($highFilter, $questionnaire->getGeoname(), $rule, $part);
                }
            }
        }
    }

    /**
     * Import exclude rules based on Yes/No content on cells
     * @param \PHPExcel_Worksheet $sheet
     * @param integer $col
     * @param Questionnaire $questionnaire
     */
    protected function importExcludes(\PHPExcel_Worksheet $sheet, $col, Questionnaire $questionnaire, Filter $filter, array $filterData)
    {
        $row = $filterData['excludes'];
        if (!$row) {
            return;
        }

        foreach ($this->partOffsets as $offset => $part) {
            $includedValue = $this->getCalculatedValueSafely($sheet->getCellByColumnAndRow($col + $offset, $row));

            // If it is not included, then it means we need an exclude rule
            if (strtolower($includedValue) != 'yes') {
                $this->getFilterQuestionnaireUsage($filter, $questionnaire, $this->excludeRule, $part, true);
            }
        }
    }

    /**
     * Finish the special cases of ratio used for high filters: "Sanitation - Improved" and "Sanitation - Shared"
     * We define those filter as being the Ratio itself
     * @return void
     */
    protected function finishRatios()
    {
        $repository = $this->getEntityManager()->getRepository('Application\Model\Rule\QuestionnaireUsage');
        $synonyms = array(
            'Facilités améliorées partagées / Facilités améliorées',
            'Shared facilities / All improved facilities',
            'Shared improved facilities/all improved facilities',
        );

        $filterImproved = @$this->cacheHighFilters['Improved'];
        $filterShared = @$this->cacheHighFilters['Shared'];

        // SKip everything if we are not in Sanitation
        if (!$filterImproved || !$filterShared) {
            return;
        }

        $ratios = $repository->getAllByRuleName($synonyms, $this->importedQuestionnaires);
        foreach ($ratios as $ratio) {

            $ruleId = $ratio->getRule()->getId();
            $questionnaireId = $ratio->getQuestionnaire()->getId();
            $ratioReference = "{R#$ruleId,Q#$questionnaireId,P#current}";

            $formulaImproved = "=1 - $ratioReference";
            $this->linkRule($formulaImproved, $filterImproved, $ratio->getQuestionnaire(), $ratio->getPart());

            $formulaShared = "=$ratioReference";
            $this->linkRule($formulaShared, $filterShared, $ratio->getQuestionnaire(), $ratio->getPart());
            echo '.';
        }
    }

    /**
     * Create (or get) a rule and link it to the given filter
     * @param string $formula
     * @param Filter $filter
     * @param Questionnaire $questionnaire
     * @param Part $part
     */
    protected function linkRule($formula, Filter $filter, Questionnaire $questionnaire, Part $part)
    {
        $name = $filter->getName() . ' (' . $questionnaire->getName() . ', ' . $part->getName() . ')';
        $rule = $this->getRule($name, $formula);
        $this->getFilterQuestionnaireUsage($filter, $questionnaire, $rule, $part, true);
    }

}
