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
    private $cacheSurvey = array();
    private $cacheFilters = array();
    private $cacheQuestionnaireUsages = array();
    private $cacheFilterQuestionnaireUsages = array();
    private $cacheFilterGeonameUsages = array();
    private $cacheFormulas = array();
    private $cacheQuestions = array();
    private $cacheHighFilters = array();
    private $importedQuestionnaires = array();
    private $colToParts = array();
    private $surveyCount = 0;
    private $questionnaireCount = 0;
    private $answerCount = 0;
    private $excludeCount = 0;
    private $ruleCount = 0;
    private $questionnaireUsageCount = 0;
    private $filterQuestionnaireUsageCount = 0;
    private $filterGeonameUsageCount = 0;
    private $ratioSynonyms = array(
        'Facilités améliorées partagées / Facilités améliorées',
        'Shared facilities / All improved facilities',
        'Shared improved facilities/all improved facilities',
    );

    /**
     * @var \PHPExcel_Worksheet
     */
    private $sheet;

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
     * The very special filter used to store ratio necessary to compute Shared and Improved.
     * This is only available for Sanitation
     * @var Filter
     */
    private $filterForRatio;

    /**
     * Import data from file
     * @param string $filename
     */
    public function import($filename)
    {
        $reader = \PHPExcel_IOFactory::createReaderForFile($filename);
        $reader->setReadDataOnly(true);

        $sheetNamesToImport = array_keys($this->definitions);
        $reader->setLoadSheetsOnly($sheetNamesToImport);
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

        foreach ($sheetNamesToImport as $i => $sheetName) {

            $this->cacheQuestions = array();
            $this->importFilters($this->definitions[$sheetName]);

            // Also create a filterSet with same name for the first filter
            $firstFilter = $this->cacheFilters[4];
            $filterSetRepository = $this->getEntityManager()->getRepository('Application\Model\FilterSet');
            $filterSet = $filterSetRepository->getOrCreate($firstFilter->getName());
            foreach ($firstFilter->getChildren() as $child) {
                $filterSet->addFilter($child);
            }

            // Import high filter, but not their formula, we need them before importing QuestionnaireUsages
            $this->importHighFilters($this->definitions[$sheetName]['filterSet'], $this->definitions[$sheetName]['highFilters']);

            // Import super-special filter only for Sanitation
            if ($sheetName == 'Tables_S') {
                $name = 'Ratio of Shared improved facilities / All improved facilities';
                $this->filterForRatio = $this->getFilter([$name, 4], $this->cacheFilters);
                $filterSet->addFilter($this->filterForRatio);
            }

            $workbook->setActiveSheetIndex($i);
            $this->sheet = $workbook->getSheet($i);

            // Try to import the first 50 questionnaires if data found
            // According to tab GraphData_W, maximum should be 40, but better safe than sorry
            $this->colToParts = array();
            for ($col = 0; $col < 50 * 6; $col += 6) {
                $this->importQuestionnaire($col);
            }
            $this->getEntityManager()->flush();

            // Second pass on imported questionnaires to process cross-questionnaire things
            echo 'Importing uses of Rule for Questionnaire';
            foreach ($this->importedQuestionnaires as $col => $questionnaire) {
                $this->importQuestionnaireUsages($col, $questionnaire);
                echo '.';
            }
            echo PHP_EOL;

            // Third pass to import first step formulas for high filters
            $this->importFilterQuestionnaireUsages($this->definitions[$sheetName]['highFilters']);

            // Fourth pass to import last step formulas for high filters
            $this->importFilterGeonameUsages($this->definitions[$sheetName]['highFilters']);

            // Fourth pass to hardcode special cases of formulas
            $this->finishRatios($this->definitions[$sheetName]['highFilters']);
            echo PHP_EOL;
        }

        $this->getEntityManager()->flush();

        $answerRepository = $this->getEntityManager()->getRepository('Application\Model\Answer');
        $answerRepository->completePopulationAnswer();

        $this->cleanUpRatios();

        return <<<STRING

Surveys          : $this->surveyCount
Questionnaires   : $this->questionnaireCount
Answers          : $this->answerCount
Rules            : $this->ruleCount
Uses of Exclude  : $this->excludeCount
Uses of Rule for Questionnaire       : $this->questionnaireUsageCount
Uses of Rule for Filter-Questionnaire: $this->filterQuestionnaireUsageCount
Uses of Rule for Filter-Geoname      : $this->filterGeonameUsageCount

STRING;
    }

    /**
     * Standardize Survey code
     * @param string $code
     * @param string $year
     * @return string standardized code
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

        // Sometimes the year and code does not match, we fix it here. Eg: JMP99, 1998 => JMP98, 1998
        if ($code == 'JMP99' && $year == 1998) {
            $code = 'JMP98';
        } elseif ($code == 'CEN93' && $year == 1990) {
            $code = 'CEN90';
        }

        return $code;
    }

    /**
     * Import a questionnaire from the given column offset.
     * Questionnaire and Answers will always be created new. All other objects will be retrieved from database if available.
     * @param integer $col
     * @return void
     */
    private function importQuestionnaire($col)
    {
        $code = trim($this->sheet->getCellByColumnAndRow($col + 2, 1)->getCalculatedValue());

        // If no code found, we assume no survey at all
        if (!$code) {
            return;
        }

        $year = $this->sheet->getCellByColumnAndRow($col + 3, 3)->getCalculatedValue();
        $code = $this->standardizeSurveyCode($code, $year);

        // Load or create survey
        $surveyRepository = $this->getEntityManager()->getRepository('Application\Model\Survey');
        if (array_key_exists($code, $this->cacheSurvey)) {
            $survey = $this->cacheSurvey[$code];
        } else {
            $survey = $surveyRepository->findOneBy(array('code' => $code));
        }

        if (!$survey) {
            $survey = new Survey($this->sheet->getCellByColumnAndRow($col + 0, 2)->getCalculatedValue());

            $survey->setIsActive(true);
            $survey->setCode($code);
            $survey->setYear($year);

            if (!$survey->getName()) {
                $survey->setName($survey->getCode());
            }

            if (!$survey->getYear()) {
                echo 'WARNING: skipped survey because there is no year. On sheet "' . $this->sheet->getTitle() . '" cell ' . $this->sheet->getCellByColumnAndRow($col + 3, 3)->getCoordinate() . PHP_EOL;

                return;
            }
            $this->getEntityManager()->persist($survey);
            $this->surveyCount++;
        }
        $this->cacheSurvey[$code] = $survey;

        // Create questionnaire
        $countryCell = $this->sheet->getCellByColumnAndRow($col + 3, 1);
        $questionnaire = $this->getQuestionnaire($col, $survey, $countryCell);
        if (!$questionnaire) {
            echo 'WARNING: skipped questionnaire because there is no country name. On sheet "' . $this->sheet->getTitle() . '" cell ' . $countryCell->getCoordinate() . PHP_EOL;

            return;
        }

        echo 'Survey: ' . $survey->getCode() . PHP_EOL;
        echo 'Country: ' . $questionnaire->getGeoname()->getName() . PHP_EOL;

        $this->importAnswers($col, $survey, $questionnaire);
        $this->importExtras($col, $questionnaire);

        // Keep a trace of what column correspond to what questionnaire for second pass
        $this->importedQuestionnaires[$col] = $questionnaire;
        foreach ($this->partOffsets as $offset => $part) {
            $this->colToParts[$col + $offset]['questionnaire'] = $questionnaire;
            $this->colToParts[$col + $offset]['part'] = $part;
        }
    }

    /**
     * Import extra data in questionnaire comments
     * Questions will only be created if an answer exists.
     * @param integer $col
     * @param Questionnaire $questionnaire
     */
    private function importExtras($col, Questionnaire $questionnaire)
    {
        foreach ($this->definitions[$this->sheet->getTitle()]['extras'] as $row => $title) {

            $comment = $title . ':' . PHP_EOL;
            $shouldAppend = false;

            foreach ($this->partOffsets as $offset => $part) {
                $value = $this->sheet->getCellByColumnAndRow($col + $offset, $row)->getCalculatedValue();

                // Remove second dot in number (eg: '123.456.789' => '123.456789')
                $value = preg_replace('/(\.[^.]*)(\.)/', '$1', $value);

                if ($value == 'NA') {
                    $value = '';
                }

                if ($value != '') {
                    $shouldAppend = true;
                }

                $comment .= '* ' . $part->getName() . ': ' . $value . PHP_EOL;
            }

            if ($shouldAppend) {
                $questionnaire->appendComment($comment);
            }
        }
    }

    /**
     * Import all answers found at given column offset.
     * Questions will only be created if an answer exists.
     * @param integer $col
     * @param Survey $survey
     * @param Questionnaire $questionnaire
     */
    private function importAnswers($col, Survey $survey, Questionnaire $questionnaire)
    {
        $knownRows = array_keys($this->cacheFilters);
        array_shift($knownRows); // Skip first filter, since it's not an actual row, but the common "JMP" filter
        array_shift($knownRows); // Skip second filter, since it's not an actual row, but the sheet topic (eg: "Access to drinking water sources")
        // Remove negative rows which were replacement filters
        $knownRows = array_filter($knownRows, function ($row) {
            return $row > 0 && $row < 100;
        });

        $answerCount = 0;
        foreach ($knownRows as $row) {

            $filter = $this->cacheFilters[$row];

            // Get alternate question name, if any
            $alternateName = trim($this->sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue());

            // Import answers for each parts
            $question = null;
            foreach ($this->partOffsets as $offset => $part) {
                $answerCell = $this->sheet->getCellByColumnAndRow($col + $offset, $row);

                // Only import value which are numeric, and NOT formula,
                // unless an question name is defined, in this case we will import the formula result
                if ($alternateName || $answerCell->getDataType() == \PHPExcel_Cell_DataType::TYPE_NUMERIC) {

                    // If there is actually no value, skip it (need to be done after previous IF to avoid formula exception within PHPExcel)
                    $value = $this->getCalculatedValueSafely($answerCell);
                    if (!is_numeric($value) || ($value == 0 && $answerCell->getDataType() == \PHPExcel_Cell_DataType::TYPE_FORMULA)) {
                        continue;
                    }

                    if (!$question) {
                        $question = $this->getQuestion($questionnaire, $filter, $alternateName);
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
     * It will only get a questionnaire from previous tab, so only if we are in Sanitation.
     * In all other cases a new questionnaire will be created.
     * @param integer $col
     * @param \Application\Model\Survey $survey
     * @param \PHPExcel_Cell $countryCell
     * @return null|\Application\Model\Questionnaire
     * @throws \Exception
     */
    private function getQuestionnaire($col, Survey $survey, \PHPExcel_Cell $countryCell)
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
        $questionnaire->setStatus(\Application\Model\QuestionnaireStatus::$PUBLISHED);
        $questionnaire->setSurvey($survey);
        $questionnaire->setDateObservationStart(new \DateTime($survey->getYear() . '-01-01'));
        $questionnaire->setDateObservationEnd(new \DateTime($survey->getYear() . '-12-31T23:59:59'));
        $questionnaire->setGeoname($geoname);
        $questionnaire->appendComment($this->sheet->getCellByColumnAndRow($col + 0, 3)->getCalculatedValue());

        $this->getEntityManager()->persist($questionnaire);
        $this->questionnaireCount++;

        return $questionnaire;
    }

    /**
     * Some files have a buggy self-referencing formula, so we need to fallback on cached result of formula
     * @param \PHPExcel_Cell $cell
     * @return type
     * @throws \PHPExcel_Exception
     */
    private function getCalculatedValueSafely(\PHPExcel_Cell $cell)
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
     * Returns a question either from database, or newly created
     * @param Questionnaire $questionnaire
     * @param Filter $filter
     * @param string|null $alternateName
     * @return NumericQuestion
     */
    private function getQuestion(Questionnaire $questionnaire, Filter $filter, $alternateName)
    {
        $survey = $questionnaire->getSurvey();
        $questionRepository = $this->getEntityManager()->getRepository('Application\Model\Question\NumericQuestion');

        $key = \Application\Utility::getVolatileCacheKey([$survey, $filter]);

        $question = null;
        if (array_key_exists($key, $this->cacheQuestions)) {
            $question = $this->cacheQuestions[$key];
        } elseif ($survey->getId() && $filter->getId()) {
            $question = $questionRepository->findOneBy(array('survey' => $survey, 'filter' => $filter));
        }

        if (!$question) {
            $question = new NumericQuestion($filter->getName());
            $this->getEntityManager()->persist($question);

            $question->setSurvey($survey);
            $question->setFilter($filter);
            $question->setSorting($survey->getQuestions()->count());
            $question->setParts(new \Doctrine\Common\Collections\ArrayCollection(array($this->partRural, $this->partUrban, $this->partTotal)));
            $question->setIsPopulation(true);
            $this->getEntityManager()->persist($question);
        }

        if ($alternateName) {
            if (!$questionnaire->getId()) {
                $this->getEntityManager()->flush();
            }
            $question->addAlternateName($questionnaire, $alternateName);
        }

        $this->cacheQuestions[$key] = $question;

        return $question;
    }

    /**
     * Import all rules on the questionnaire level (Calculations, Estimates and Ratios)
     * @param integer $col
     * @param Questionnaire $questionnaire
     */
    private function importQuestionnaireUsages($col, Questionnaire $questionnaire)
    {
        foreach ($this->definitions[$this->sheet->getTitle()]['questionnaireUsages'] as $group) {
            foreach ($group as $row) {
                foreach ($this->partOffsets as $offset => $part) {
                    $this->getQuestionnaireUsage($col, $row, $offset, $questionnaire, $part);
                }
            }
        }
    }

    /**
     * Create or get a QuestionnaireUsage and its Formula
     * @param integer $col
     * @param integer $row
     * @param integer $offset
     * @param Questionnaire $questionnaire
     * @param Part $part
     * @return QuestionnaireUsage|null
     */
    private function getQuestionnaireUsage($col, $row, $offset, Questionnaire $questionnaire, Part $part)
    {
        $name = $this->getCalculatedValueSafely($this->sheet->getCellByColumnAndRow($col + 1, $row));
        $value = $this->getCalculatedValueSafely($this->sheet->getCellByColumnAndRow($col + $offset, $row));

        if ($name && !is_null($value) || $value != 0) {

            $rule = $this->getRuleFromCell($col, $row, $offset, $questionnaire, $part);

            // If formula was non-existing, or invalid, cannot do anything more
            if (!$rule) {
                return null;
            }

            // If we had an existing formula, maybe we also have an existing usage
            $usage = $questionnaire->getQuestionnaireUsages()->filter(function ($usage) use ($questionnaire, $part, $rule) {
                return $usage->getQuestionnaire() === $questionnaire && $usage->getPart() === $part && $usage->getRule() === $rule;
            })->first();

            // If usage doesn't exist yet, create it
            if (!$usage) {
                $usage = new QuestionnaireUsage();
                $usage->setJustification($this->defaultJustification)->setQuestionnaire($questionnaire)->setRule($rule)->setPart($part);

                $this->getEntityManager()->persist($usage);
                $this->questionnaireUsageCount++;
            }
            $this->cacheQuestionnaireUsages[] = $usage;

            return $usage;
        }

        return null;
    }

    /**
     * Create or get a formula by converting Excel syntax to our own syntax
     * @param integer $col
     * @param integer $row
     * @param integer $offset
     * @param Questionnaire $questionnaire
     * @param Part $part
     * @param string $forcedName
     * @return null|Rule
     */
    private function getRuleFromCell($col, $row, $offset, Questionnaire $questionnaire, Part $part, $forcedName = null)
    {
        $cell = $this->sheet->getCellByColumnAndRow($col + $offset, $row);
        $originalFormula = $cell->getValue();

        // if we have nothing at all, or stricly only letters, cannot do anything
        if (is_null($originalFormula) || $originalFormula == '' || preg_match('/^[a-zA-Z]+$/', $originalFormula)) {
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
        if (preg_match("/$cellPattern/", $originalFormula)) $replacedFormula = str_replace(array('*100', '/100'), '', $originalFormula); else {
            $replacedFormula = $originalFormula;
        }

        // For the same reason, we need to replace complementary computing based on 100, to be based on 1
        // eg: "=100-A23" => "=100%-A23"
        $replacedFormula = preg_replace('/([^a-zA-Z])100-/', '${1}100%-', $replacedFormula);

        // Some formulas, Estimations and Calculation, hardcode values as percent between 0 - 100,
        // we need to convert them to 0.00 - 1.00
        $ruleRows = $this->definitions[$this->sheet->getTitle()]['questionnaireUsages'];
        if (in_array($row, $ruleRows['Estimate']) || in_array($row, $ruleRows['Calculation'])) {

            // Convert very simple formula with numbers only and -/+ operations. Anything more complex
            // would be too dangerous. This is the case for Cambodge DHS05 "Bottled water with HC" estimation, or
            // Solomon Islands, Tables_W!AH88
            // eg: "=29.6" => "=29.6%", "=29.6+10" => "=39.6%"
            $replacedFormula = \Application\Utility::pregReplaceUniqueCallback('/^=[-+\.\d ]+$/', function ($matches) {
                $number = \PHPExcel_Calculation::getInstance()->_calculateFormulaValue($matches[0]);
                $number = $number . '%';

                return "=$number";
            }, $replacedFormula);

            // Convert when using a Ratio, this is the case of Thailand, Tables_W!CD88
            // eg: "=44.6*BR102" => "=44.6%*BR102"
            $replacedFormula = \Application\Utility::pregReplaceUniqueCallback("/^=([-+\.\d ]+)(\*$cellPattern)$/", function ($matches) use ($ruleRows) {
                $number = $matches[1];

                if (in_array($matches[5], $ruleRows['Ratio'])) {
                    $number = $number . '%';
                }

                return "=$number" . $matches[2];
            }, $replacedFormula);
        }

        // Some formulas, Ratios, hardcode values as percent between 0.00 - 1.00,
        // while this is technically correct, we prefer the notation with %, so we convert them here
        $ruleRows = $this->definitions[$this->sheet->getTitle()]['questionnaireUsages'];
        if (in_array($row, $ruleRows['Ratio'])) {

            // Convert very simple formula with numbers only. Anything more complex
            // would be too dangerous.
            // eg: "=0.296" => "=29.6%", "=0.296+0.10" => "=39.6%"
            $replacedFormula = \Application\Utility::pregReplaceUniqueCallback('/^=[-+\/\*\.\d ]+$/', function ($matches) {
                $number = \PHPExcel_Calculation::getInstance()->_calculateFormulaValue($matches[0]);
                $number = ($number * 100) . '%';

                return "=$number";
            }, $replacedFormula);
        }

        // Expand range syntax to enumerate each cell: "A1:A5" => "{A1,A2,A3,A4,A5}"
        $expandedFormula = \Application\Utility::pregReplaceUniqueCallback("/$cellPattern:$cellPattern/", function ($matches) use ($cell) {

            // This only expand vertical ranges, not horizontal ranges (which probably never make any sense for JMP anyway)
            if ($matches[2] != $matches[5]) {
                throw new \Exception('Horizontal range are not supported: ' . $matches[0] . ' found in ' . $this->sheet->getTitle() . ', cell ' . $cell->getCoordinate());
            }

            $expanded = array();
            for ($i = $matches[3]; $i <= $matches[6]; $i++) {
                $expanded[] = $matches[2] . $i;
            }

            return '{' . join(',', $expanded) . '}';
        }, $replacedFormula);

        // Replace special cell reference with some hardcoded formulas
        $expandedFormula = \Application\Utility::pregReplaceUniqueCallback("/$cellPattern/", function ($matches) use ($cell) {

            $replacement = @$this->definitions[$this->sheet->getTitle()]['cellReplacements'][$matches[3]];
            if ($replacement) {
                // Use same column for replacement as the original cell reference
                $replacement = str_replace('A', $matches[2], $replacement);

                return '(' . $replacement . ')';
            } else {
                return $matches[0];
            }
        }, $expandedFormula);

        // Replace all cell reference with our own syntax
        $convertedFormula = \Application\Utility::pregReplaceUniqueCallback("/$cellPattern/", function ($matches) use ($questionnaire, $part, $expandedFormula) {
            $refCol = \PHPExcel_Cell::columnIndexFromString($matches[2]) - 1;
            $refRow = $matches[3];

            // We first look for filter on negative indexes to find original filters in case we made
            // replacements (for Sanitation), because formulas always refers to the originals,
            // not the one we created in this script
            $refFilter = @$this->cacheFilters[-$refRow] ? : @$this->cacheFilters[$refRow];

            // If couldn't find filter yet, try last chance in high filters
            if (!$refFilter) {
                foreach ($this->definitions[$this->sheet->getTitle()]['highFilters'] as $highFilterName => $highFilterData) {
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
            if ($refQuestionnaire === $questionnaire) $refQuestionnaireId = 'current'; else
                $refQuestionnaireId = $refQuestionnaire->getId();

            // Find out referenced Part
            $refPart = $refData['part'];
            if ($refPart === $part) {
                $refPartId = 'current';
            } else {
                $refPartId = $refPart->getId();
            }

            // Simple case is when we reference a filter
            if ($refFilterId) {
                return "{F#$refFilterId,Q#$refQuestionnaireId,P#$refPartId}";
                // More advanced case is when we reference another QuestionnaireUsage (Calculation, Estimate or Ratio)
            } else {

                // Find the column of the referenced questionnaire
                $refColQuestionnaire = array_search($refQuestionnaire, $this->importedQuestionnaires);

                $refQuestionnaireUsage = $this->getQuestionnaireUsage($refColQuestionnaire, $refRow, $refCol - $refColQuestionnaire, $refQuestionnaire, $refPart);

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
        foreach ($this->definitions[$this->sheet->getTitle()]['questionnaireUsages'] as $label => $rows) {
            if (in_array($row, $rows)) {
                $prefix = $label . ': ';
            }
        }

        $name = $forcedName ? : $this->getCalculatedValueSafely($this->sheet->getCellByColumnAndRow($col + 1, $row));

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
    private function getRule($name, $formula)
    {
        // If formula is only a number, we allow duplicated rules.
        // Because it would most likely not have sense to have the same number
        // used in different context with a unique name. Also it used to break
        // the special cases of ratio for Shared/Improved if the ratio was a number
        // which was already in use somewhere else
        $onlyNumbers = preg_match('/^=[\d\.%+-]+$/', $formula);

        $rule = null;
        $key = \Application\Utility::getVolatileCacheKey(array($formula));
        if (!$onlyNumbers) {
            if (array_key_exists($key, $this->cacheFormulas)) {
                return $this->cacheFormulas[$key];
            }

            $ruleRepository = $this->getEntityManager()->getRepository('Application\Model\Rule\Rule');

            // Look for existing formula (to prevent duplication)
            $rule = $ruleRepository->findOneByFormula($formula);
        }

        if (!$rule) {
            $rule = new Rule($name);
            $rule->setFormula($formula);
            $this->getEntityManager()->persist($rule);
            $this->ruleCount++;
        }

        $this->cacheFormulas[$key] = $rule;

        return $rule;
    }

    /**
     * Finish high filters, by importing their exclude rules and their formulas (if any)
     * @param array $filters
     */
    private function importFilterQuestionnaireUsages(array $filters)
    {
        $complementaryTotalFormula = $this->getRule('Total part is sum of parts if both are available', '=IF(AND(ISNUMBER({F#current,Q#current,P#' . $this->partRural->getId() . ',S#2}), ISNUMBER({F#current,Q#current,P#' . $this->partUrban->getId() . ',S#2})), ({F#current,Q#current,P#' . $this->partRural->getId() . ',S#2} * {Q#current,P#' . $this->partRural->getId() . '} + {F#current,Q#current,P#' . $this->partUrban->getId() . ',S#2} * {Q#current,P#' . $this->partUrban->getId() . '}) / {Q#current,P#' . $this->partTotal->getId() . '}, {self})');
        // Get or create all filter
        echo 'Importing uses of Rule for Filter-Questionnaire';
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
                        $rule = $this->getRuleFromCell($col, $filterData['row'], $offset, $questionnaire, $part, $filterName . ' (' . $questionnaire->getName() . ($part ? ', ' . $part->getName() : '') . ')');
                        if ($rule) {
                            $this->getFilterQuestionnaireUsage($highFilter, $questionnaire, $rule, $part);
                        }
                    }

                    // If the high filter has some hardcoded formula, import them as well
                    if (isset($filterData['rule'])) {
                        $formula = $this->replaceHighFilterNamesWithIdForBeforeRegression($filters, $filterData['rule']);
                        $rule = $this->getRule(($this->sheet->getTitle() == 'Tables_W' ? 'Water - ' : 'Sanitation - ') . $filterName, $formula);
                        $this->getFilterQuestionnaireUsage($highFilter, $questionnaire, $rule, $part);
                    }
                }

                $this->importExcludes($questionnaire, $highFilter, $filterData['excludes']);
            }
            echo '.';
        }

        echo PHP_EOL;
    }

    /**
     * Create or get a filterQuestionnaireUsage
     * @param Filter $filter
     * @param Questionnaire $questionnaire
     * @param Rule $rule
     * @param Part $part
     * @param boolean $isSecondStep
     * @return FilterQuestionnaireUsage
     */
    private function getFilterQuestionnaireUsage(Filter $filter, Questionnaire $questionnaire, Rule $rule, Part $part, $isSecondStep = false)
    {
        $key = \Application\Utility::getVolatileCacheKey(func_get_args());
        if (array_key_exists($key, $this->cacheFilterQuestionnaireUsages)) {
            $filterQuestionnaireUsage = $this->cacheFilterQuestionnaireUsages[$key];
        } else {
            $filterQuestionnaireUsage = new FilterQuestionnaireUsage();
            $filterQuestionnaireUsage->setJustification($this->defaultJustification)->setQuestionnaire($questionnaire)->setRule($rule)->setPart($part)->setFilter($filter)->setIsSecondStep($isSecondStep);

            $this->getEntityManager()->persist($filterQuestionnaireUsage);

            if ($rule === $this->excludeRule) {
                $this->excludeCount++;
            } else {
                $this->filterQuestionnaireUsageCount++;
            }

            $this->cacheFilterQuestionnaireUsages[$key] = $filterQuestionnaireUsage;
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
    private function getFilterGeonameUsage(Filter $filter, Geoname $geoname, Rule $rule, Part $part)
    {
        $filterGeonameUsage = null;
        $key = \Application\Utility::getVolatileCacheKey(func_get_args());
        if (array_key_exists($key, $this->cacheFilterGeonameUsages)) {
            $filterGeonameUsage = $this->cacheFilterGeonameUsages[$key];
        } elseif ($rule->getId() && $part->getId() && $filter->getId() && $geoname->getId()) {
            $repository = $this->getEntityManager()->getRepository('Application\Model\Rule\FilterGeonameUsage');
            $filterGeonameUsage = $repository->findOneBy(array(
                'rule' => $rule,
                'part' => $part,
                'filter' => $filter,
                'geoname' => $geoname,
            ));
        }

        if (!$filterGeonameUsage) {
            $filterGeonameUsage = new FilterGeonameUsage();
            $filterGeonameUsage->setJustification($this->defaultJustification)->setRule($rule)->setPart($part)->setFilter($filter)->setGeoname($geoname);

            $this->getEntityManager()->persist($filterGeonameUsage);
            $this->filterGeonameUsageCount++;
        }

        $this->cacheFilterGeonameUsages[$key] = $filterGeonameUsage;

        return $filterGeonameUsage;
    }

    /**
     * Import FilterGeonameUsage for all highfilters
     * @param array $filters
     */
    private function importFilterGeonameUsages(array $filters)
    {
        echo 'Importing uses of Rule for Filter-Geoname';
        foreach ($this->importedQuestionnaires as $questionnaire) {

            $formulaGroup = 'default';
            $countryName = $questionnaire->getGeoname()->getCountry()->getName();
            if (in_array($countryName, array(
                'American Samoa',
                'Antigua and Barbuda',
                'Aruba',
                'Bahamas',
                'Bahrain',
                'Barbados',
                'British Virgin Islands',
                'Cook Islands',
                'French Polynesia',
                'Guam',
                'Montserrat',
                'New Caledonia',
                'Niue',
                'Northern Mariana Islands',
                'Puerto Rico',
                'Saint Kitts and Nevis',
                'Saint Vincent and the Grenadines',
                'Seychelles',
                'Turks and Caicos Islands',
                'United States Virgin Islands'
            ))
            ) {
                $formulaGroup = 'onlyTotal';
            } elseif (in_array($countryName, array(
                'Tokelau',
            ))
            ) {
                $formulaGroup = 'onlyRural';
            } elseif (in_array($countryName, array(
                'Anguilla',
                'Cayman Islands',
                'Monaco',
                'Nauru',
                'Singapore',
            ))
            ) {
                $formulaGroup = 'onlyUrban';
            } elseif (in_array($countryName, array(
                'Argentina',
                'Chile',
                'Costa Rica',
                'Guadeloupe',
                'Iraq',
                'Lithuania',
                'Martinique',
                'Palau',
                'Portugal',
                'Tuvalu',
                'Uruguay',
            ))
            ) {
                $formulaGroup = 'popHigherThanTotal';
            } elseif (in_array($countryName, array(
                'Belarus',
                'TFYR Macedonia',
                'United States of America',
                'Saudi Arabia',
                'Tunisia',
            ))
            ) {

                // Here each country has its own set of rules, so we use country name as formulaGroup name
                $formulaGroup = $countryName;
            }

            foreach ($filters as $filterName => $filterData) {
                $highFilter = $this->cacheHighFilters[$filterName];

                $actualFormulaGroup = isset($filterData['regressionRules'][$formulaGroup]) ? $formulaGroup : 'default';
                foreach ($filterData['regressionRules'][$actualFormulaGroup] as $formulaData) {

                    $part = $this->partOffsets[$formulaData[0]];
                    $formula = $formulaData[1];
                    $isDevelopedFormula = @$formulaData[2];

                    // Only add developed formulas for developed countries
                    if ($isDevelopedFormula && !$this->isDevelopedCountry) {
                        continue;
                    }

                    // Translate our custom import syntax into real GIMS syntax
                    $formula = $this->replaceHighFilterNamesWithIdForAfterRegression($filters, $formula);

                    $suffix = ' (' . $actualFormulaGroup . ($isDevelopedFormula ? ' - for developed countries' : '') . ')';
                    $rule = $this->getRule('Regression: ' . $highFilter->getName() . $suffix, $formula);
                    $this->getFilterGeonameUsage($highFilter, $questionnaire->getGeoname(), $rule, $part);
                }
                echo '.';
            }
        }

        echo PHP_EOL;
    }

    /**
     * Replace high filter names found in formula by their IDs. Also replace a few custom syntax.
     * @param array $filters the high filters
     * @param string $formula
     * @return string
     */
    private function replaceHighFilterNamesWithIdForAfterRegression(array $filters, $formula)
    {
        if ($this->filterForRatio) {
            $id = $this->filterForRatio->getId();
            $formula = str_replace('ALL_RATIOS', "{F#$id,Q#all}", $formula);
        }

        foreach ($filters as $filterNameOther => $foo) {
            $otherHighFilter = $this->cacheHighFilters[$filterNameOther];
            $id = $otherHighFilter->getId();
            $formula = str_replace($filterNameOther . 'EARLIER', "{F#$id,P#current,Y-1}", $formula);
            $formula = str_replace($filterNameOther . 'LATER', "{F#$id,P#current,Y+1}", $formula);
            $formula = str_replace($filterNameOther . 'URBAN', "{F#$id,P#" . $this->partUrban->getId() . ",Y0}", $formula);
            $formula = str_replace($filterNameOther . 'RURAL', "{F#$id,P#" . $this->partRural->getId() . ",Y0}", $formula);
            $formula = str_replace($filterNameOther . 'TOTAL', "{F#$id,P#" . $this->partTotal->getId() . ",Y0}", $formula);
            $formula = str_replace($filterNameOther, "{F#$id,P#current,Y0}", $formula);
            $formula = str_replace('POPULATION_URBAN', "{Q#all,P#" . $this->partUrban->getId() . "}", $formula);
            $formula = str_replace('POPULATION_RURAL', "{Q#all,P#" . $this->partRural->getId() . "}", $formula);
            $formula = str_replace('POPULATION_TOTAL', "{Q#all,P#" . $this->partTotal->getId() . "}", $formula);
        }

        return $formula;
    }

    /**
     * Replace high filter names found in formula by their IDs.
     * @param array $filters the high filters
     * @param string $formula
     * @return string
     */
    private function replaceHighFilterNamesWithIdForBeforeRegression(array $filters, $formula)
    {
        foreach ($filters as $filterNameOther => $foo) {
            $otherHighFilter = $this->cacheHighFilters[$filterNameOther];
            $id = $otherHighFilter->getId();
            $formula = str_replace($filterNameOther . 'REGRESSION', "{F#$id,P#current,Y+0}", $formula);
            $formula = str_replace($filterNameOther, "{F#$id,Q#current,P#current,S#2}", $formula);
        }

        return $formula;
    }

    /**
     * Import exclude rules based on Yes/No content in cells
     * @param Questionnaire $questionnaire
     * @param Filter $filter
     * @param integer $row
     */
    private function importExcludes(Questionnaire $questionnaire, Filter $filter, $row)
    {
        $col = array_search($questionnaire, $this->importedQuestionnaires, true);
        if (!$row || $col === false) {
            return;
        }

        $this->importedQuestionnaires;
        foreach ($this->partOffsets as $offset => $part) {
            $includedValue = $this->getCalculatedValueSafely($this->sheet->getCellByColumnAndRow($col + $offset, $row));

            // If it is not included, then it means we need an exclude rule
            if (strtolower($includedValue) != 'yes') {
                $this->getFilterQuestionnaireUsage($filter, $questionnaire, $this->excludeRule, $part, true);
            }
        }
    }

    /**
     * Finish the special cases of ratio used for high filters: "Sanitation - Improved" and "Sanitation - Shared"
     * We define those filter as being the Ratio itself
     * @param array $filters
     */
    private function finishRatios(array $filters)
    {
        $filterImproved = @$this->cacheHighFilters['Improved'];
        $filterShared = @$this->cacheHighFilters['Shared'];

        // SKip everything if we are not in Sanitation
        if (!$filterImproved || !$filterShared) {
            return;
        }

        echo 'Finishing special cases of Ratios for Sanitation';

        $regexp = '-(' . join('|', $this->ratioSynonyms) . ')-i';

        // Create unique rules for everybody
        $formulaImproved = $this->replaceHighFilterNamesWithIdForBeforeRegression($filters, '=Improved + sharedREGRESSION * (100% - AVERAGE({F#' . $this->filterForRatio->getId() . ',Q#all}))');
        $formulaShared = $this->replaceHighFilterNamesWithIdForBeforeRegression($filters, '=Improved + sharedREGRESSION * AVERAGE({F#' . $this->filterForRatio->getId() . ',Q#all}))');
        $ruleImproved = $this->getRule('Improved, based on average of shared ratios', $formulaImproved);
        $ruleShared = $this->getRule('Shared, based on average of shared ratios', $formulaShared);

        $questionnairesWithExcludedImported = [];
        foreach ($this->cacheQuestionnaireUsages as $usage) {

            if (!preg_match($regexp, $usage->getRule()->getName())) {
                continue;
            }

            $this->transformRatioIntoAnswer($usage, $this->filterForRatio);

            // Apply the rules, so we have coverage data for Improved and Shared
            $questionnaire = $usage->getQuestionnaire();
            $this->getFilterQuestionnaireUsage($filterImproved, $questionnaire, $ruleImproved, $usage->getPart(), true);
            $this->getFilterQuestionnaireUsage($filterShared, $questionnaire, $ruleShared, $usage->getPart(), true);

            if (!in_array($questionnaire, $questionnairesWithExcludedImported)) {
                $this->importExcludes($questionnaire, $this->filterForRatio, 99);
                $questionnairesWithExcludedImported[] = $questionnaire;
            }
        }
    }

    private function transformRatioIntoAnswer(QuestionnaireUsage $usage, Filter $filterForRatio)
    {
        $rule = $usage->getRule();
        $formula = $rule->getFormula();

        // If formula contains GIMS syntax, transform into rule for the filter (minority of cases)
        if (preg_match('/#/', $formula)) {
            $this->getFilterQuestionnaireUsage($filterForRatio, $usage->getQuestionnaire(), $rule, $usage->getPart());
        } // Else transform into simple answer (vast majority of cases)
        else {
            $answerValue = \PHPExcel_Calculation::getInstance()->_calculateFormulaValue($formula);
            $question = $this->getQuestion($usage->getQuestionnaire(), $filterForRatio, null);

            $answer = new Answer();
            $this->getEntityManager()->persist($answer);
            $answer->setQuestionnaire($usage->getQuestionnaire());
            $answer->setQuestion($question);
            $answer->setPart($usage->getPart());
            $answer->setValuePercent($answerValue);
        }

        // We only remove usage, and not the rule itself, because we are not sure
        // if the rule has several usages and it is easier to clean rules at the very end
        $usage->getRule()->getQuestionnaireUsages()->removeElement($usage);
        $usage->getQuestionnaire()->getQuestionnaireUsages()->removeElement($usage);
        $this->getEntityManager()->remove($usage);
    }

    /**
     * Import high filters, their FilterSet
     * @param array $filterSetNames
     * @param array $filters
     */
    private function importHighFilters(array $filterSetNames, array $filters)
    {
        $filterSetRepository = $this->getEntityManager()->getRepository('Application\Model\FilterSet');
        $filterSet = $filterSetRepository->getOrCreate($filterSetNames['improvedUnimprovedName']);
        $improvedFilterSet = $filterSetRepository->getOrCreate($filterSetNames['improvedName']);
        $this->getEntityManager()->flush();

        // Get or create all filter
        echo 'Importing high filters';
        foreach ($filters as $filterName => $filterData) {

            // Look for existing high filter...
            $highFilter = null;
            foreach ($filterSet->getFilters() as $f) {
                if ($f->getName() == $filterName) {
                    $highFilter = $f;
                    break;
                }
            }

            // .. or create it
            if (!$highFilter) {
                $highFilter = new Filter($filterName);
                $filterSet->addFilter($highFilter);
                reset($this->cacheFilters)->addChild($highFilter);
                $this->getEntityManager()->persist($highFilter);

                if ($filterData['isImproved']) {
                    $improvedFilterSet->addFilter($highFilter);
                }

                if ($filterData['thematic']) {
                    $highFilter->setThematicFilter($this->cacheFilters[$filterData['thematic']]);
                }
            }

            // Affect children filters
            foreach ($filterData['children'] as $child) {
                $highFilter->addChild($this->cacheFilters[$child]);
            }

            $this->cacheHighFilters[$filterName] = $highFilter;
            echo '.';
        }

        echo PHP_EOL;
    }

    /**
     * Import filters
     * @param array $filters
     */
    private function importFilters(array $filters)
    {
        // Import filters
        $this->cacheFilters = array();
        foreach ($filters['definitions'] as $row => $definition) {
            $filter = $this->getFilter($definition, $this->cacheFilters);
            $this->cacheFilters[$row] = $filter;
        }

        // Add all summands to filters
        foreach ($filters['definitions'] as $row => $definition) {
            $filter = $this->cacheFilters[$row];
            $summands = $definition[3];
            if ($summands) {
                foreach ($summands as $summand) {
                    $s = $this->cacheFilters[$summand];
                    $filter->addSummand($s);
                }
            }
        }

        // Replace filters with their replacements, if any defined
        // This is a dirty trick to solve inconsistency in first filter of sanitation
        foreach ($filters['replacements'] as $row => $definition) {
            $replacementFilter = $this->getFilter($definition, $this->cacheFilters);
            $originalFilter = @$this->cacheFilters[$row];

            // If original filter actually exists, add the replacement as a summand, and replace it
            if ($originalFilter) {
                $originalFilter->addSummand($replacementFilter);
            }
            $this->cacheFilters[$row] = $replacementFilter;

            // Keep original filter available on negative indexes
            $this->cacheFilters[-$row] = $originalFilter;
        }

        // Add extra summand which can be one of replacement
        foreach ($filters['definitions'] as $row => $definition) {
            $filter = $this->cacheFilters[$row];
            $extraSummand = @$definition[4];
            if ($extraSummand) {
                $s = $this->cacheFilters[$extraSummand];
                $filter->addSummand($s);
            }
        }

        $this->getEntityManager()->flush();
        echo count($this->cacheFilters) . ' filters imported' . PHP_EOL;
    }

    private function cleanUpRatios()
    {
        $conditions = [];
        foreach ($this->ratioSynonyms as $s) {
            $conditions[] = "name ILIKE '%$s%'";
        }
        $synonyms = implode(' OR ', $conditions);
        $id = $this->filterForRatio->getId();

        // Convert reference to ratios to be reference to filter
        $ratioToFilter = "UPDATE rule SET formula = REGEXP_REPLACE(formula, CONCAT('R\#(',
    (
        SELECT string_agg(id::varchar, '|')
        FROM rule
        WHERE $synonyms
    )
, '),')
, 'F#$id,')

";
        //        v($ratioToFilter);
        $a = $this->getEntityManager()->getConnection()->executeUpdate($ratioToFilter);

        // Clean up non-used rules after finishRatios()
        $sql = '
            DELETE FROM rule WHERE id NOT IN (
                SELECT rule_id FROM questionnaire_usage
                UNION
                SELECT rule_id FROM filter_questionnaire_usage
                UNION
                SELECT rule_id FROM filter_geoname_usage
            )';
        $b = $this->getEntityManager()->getConnection()->executeUpdate($sql);

        echo "
$a ratio references switched to filter references
$b unused rules deleted
";
    }

}
