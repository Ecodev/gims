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

    /**
     * sheet name =>
     *      defintions =>
     *          row => name, parent row, computing type, summands rows
     * @var array
     */
    private $definitions = array(
        'Tables_W' => array(
            'definitions' => array(
                4 => array("Water", null, 0, null),
                5 => array("Tap water", 4, 0, null),
                6 => array("House connections", 5, 0, null),
                7 => array("Piped water into dwelling", 6, 0, null),
                8 => array("Piped water to yard/plot", 6, 0, null),
                9 => array("Public tap, standpipe", 5, 0, null),
                10 => array("Other", 5, 0, null),
                11 => array("Ground water", 4, 0, null),
                12 => array("Protected ground water (all protected wells or springs)", 11, 1, array(14, 26, 46, 34)),
                13 => array("Unprotected ground water (all unprotected wells or springs)", 11, 1, array(18, 38, 50)),
                14 => array("Undefined protected wells or springs", 12, 2, null),
                15 => array("Private", 14, 1, null),
                16 => array("Public", 14, 1, null),
                17 => array("Other", 14, 1, null),
                18 => array("Undefined unprotected wells or springs", 13, 2, null),
                19 => array("Private", 18, 1, null),
                20 => array("Public", 18, 1, null),
                21 => array("Other", 18, 1, null),
                22 => array("All wells", 11, 0, null),
                23 => array("Private", 22, 1, array(27, 31)),
                24 => array("Public", 22, 1, array(28, 32)),
                25 => array("Other", 22, 1, array(29, 33)),
                26 => array("Tubewell, borehole", 22, 0, null),
                27 => array("Private", 26, 0, null),
                28 => array("Public", 26, 0, null),
                29 => array("Other", 26, 0, null),
                30 => array("Traditional wells", 22, 0, null),
                31 => array("Private", 30, 1, array(35, 39)),
                32 => array("Public", 30, 1, array(36, 40)),
                33 => array("Other", 30, 1, array(37, 41)),
                34 => array("Protected well", 30, 0, null),
                35 => array("Private", 34, 0, null),
                36 => array("Public", 34, 0, null),
                37 => array("Other", 34, 0, null),
                38 => array("Unprotected well", 30, 0, null),
                39 => array("Private", 38, 0, null),
                40 => array("Public", 38, 0, null),
                41 => array("Other", 38, 0, null),
                42 => array("All springs", 11, 0, null),
                43 => array("Private", 42, 1, array(47, 51)),
                44 => array("Public", 42, 1, array(48, 52)),
                45 => array("Other", 42, 1, array(49, 53)),
                46 => array("Protected spring", 42, 0, null),
                47 => array("Private", 46, 0, null),
                48 => array("Public", 46, 0, null),
                49 => array("Other", 46, 0, null),
                50 => array("Unprotected spring", 42, 0, null),
                51 => array("Private", 50, 0, null),
                52 => array("Public", 50, 0, null),
                53 => array("Other", 50, 0, null),
                54 => array("Rainwater", 4, 0, null),
                55 => array("Covered cistern/tank", 54, 0, null),
                56 => array("Uncovered cistern/tank", 54, 0, null),
                57 => array("Bottled water", 4, 0, null),
                58 => array("with other improved", 57, 0, null),
                59 => array("without other improved", 57, 0, null),
                60 => array("Surface water", 4, 0, null),
                61 => array("River", 60, 0, null),
                62 => array("Lake", 60, 0, null),
                63 => array("Dam", 60, 0, null),
                64 => array("Pond", 60, 0, null),
                65 => array("Stream", 60, 0, null),
                66 => array("Irrigation channel", 60, 0, null),
                67 => array("Other", 60, 0, null),
                68 => array("Other improved sources", 4, 0, null),
                69 => array("Other 1", 68, 0, null),
                70 => array("Other 2", 68, 0, null),
                71 => array("Other non-improved", 4, 0, null),
                72 => array("Cart with small tank/drum", 71, 0, null),
                73 => array("Tanker truck provided", 71, 0, null),
                74 => array("Other 1", 71, 0, null),
                75 => array("Other 2", 71, 0, null),
                76 => array("DK/missing", 4, 0, null),
            ),
            'replacements' => array(
            // No replacements to make for water
            ),
            'questionnaireUsages' => array(
                'Calculation' => array(78, 79, 80, 81, 82),
                'Estimate' => array(83, 84, 85, 86, 87, 88),
                'Ratio' => array(94, 95, 96, 97, 98, 99, 100, 101, 102, 103),
            ),
            'filterSet' => array(
                'improvedName' => 'Water: use of improved sources (JMP data)',
                'improvedUnimprovedName' => 'Water: use of improved and unimproved sources (JMP data)',
            ),
            'highFilters' => array(
                "Total improved" => array(
                    'row' => 89,
                    'children' => array(5, 12, 55, 58, 68),
                    'excludes' => 91,
                    'isImproved' => true,
                    'formulas' => array(
                    ),
                ),
                "Piped onto premises" => array(
                    'row' => 90,
                    'children' => array(6),
                    'excludes' => 92,
                    'isImproved' => true,
                    'formulas' => array(
                    ),
                ),
                "Surface water" => array(
                    'row' => 87,
                    'children' => array(60),
                    'excludes' => 93,
                    'isImproved' => false,
                    'formulas' => array(
                        array(3, '=IF(ISNUMBER(Total improved), IF(Total improved >= 0.995, 0, {self}), NULL)'),
                        array(4, '=IF(ISNUMBER(Total improved), IF(Total improved >= 0.995, 0, {self}), NULL)'),
                        array(5, '=IF(ISNUMBER(Total improved), IF(Total improved = 1, 0, {self}), NULL)'),
                    ),
                ),
                "Other Improved" => array(
                    'row' => null,
                    'children' => array(9, 10, 12, 55, 58, 68),
                    'excludes' => null,
                    'isImproved' => false,
                    'formulas' => array(
                        array(3, '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)'),
                        array(4, '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)'),
                        array(5, '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)'),
                    ),
                ),
                "Other Unimproved" => array(
                    'row' => null,
                    'children' => array(56, 59, 71),
                    'excludes' => null,
                    'isImproved' => false,
                    'formulas' => array(
                        array(3, '=IF(ISNUMBER(Total improved), IF(Total improved = 1, 0, 1 - Total improved - Surface water), NULL)'),
                        array(4, '=IF(ISNUMBER(Total improved), IF(Total improved = 1, 0, 1 - Total improved - Surface water), NULL)'),
                        array(5, '=IF(ISNUMBER(Total improved), IF(Total improved = 1, 0, 1 - Total improved - Surface water), NULL)'),
                    ),
                ),
            ),
        ),
        'Tables_S' => array(
            'definitions' => array(
                4 => array("Sanitation", null, 0, null),
                5 => array("Flush and pour flush", 4, 2, array(11, 30), 999),
                6 => array("to piped sewer system", 5, 1, array(12, 31)),
                7 => array("to septic tank", 5, 1, array(13, 32)),
                8 => array("to pit", 5, 1, array(14, 33)),
                9 => array("to unknown place/ not sure/DK", 5, 1, array(15, 34)),
                10 => array("to elsewhere", 5, 1, array(16, 35)),
                11 => array("Flush/toilets", 4, 0, null),
                12 => array("to piped sewer system", 11, 1, array(18, 24)),
                13 => array("to septic tank", 11, 1, array(19, 25)),
                14 => array("to pit", 11, 1, array(20, 26)),
                15 => array("to unknown place/ not sure/DK", 11, 1, array(21, 27)),
                16 => array("to elsewhere", 11, 1, array(22, 28)),
                17 => array("Private flush/toilet", 11, 0, null),
                18 => array("to piped sewer system", 17, 0, null),
                19 => array("to septic tank", 17, 0, null),
                20 => array("to pit", 17, 0, null),
                21 => array("to unknown place/ not sure/DK", 17, 0, null),
                22 => array("to elsewhere", 17, 0, null),
                23 => array("Public/shared flush/toilet", 11, 0, null),
                24 => array("to piped sewer system", 23, 0, null),
                25 => array("to septic tank", 23, 0, null),
                26 => array("to pit", 23, 0, null),
                27 => array("to unknown place/ not sure/DK", 23, 0, null),
                28 => array("to elsewhere", 23, 0, null),
                29 => array("Latrines", 4, 0, null),
                30 => array("Pour flush latrines", 29, 0, null),
                31 => array("to piped sewer system", 30, 1, array(37, 43)),
                32 => array("to septic tank", 30, 1, array(38, 44)),
                33 => array("to pit", 30, 1, array(39, 45)),
                34 => array("to unknown place/ not sure/DK", 30, 1, array(40, 46)),
                35 => array("to elsewhere", 30, 1, array(41, 47)),
                36 => array("Private pour flush latrine", 30, 0, null),
                37 => array("to piped sewer system", 36, 0, null),
                38 => array("to septic tank", 36, 0, null),
                39 => array("to pit", 36, 0, null),
                40 => array("to unknown place/ not sure/DK", 36, 0, null),
                41 => array("to elsewhere", 36, 0, null),
                42 => array("Public/shared pour flush latrine", 30, 0, null),
                43 => array("to piped sewer system", 42, 0, null),
                44 => array("to septic tank", 42, 0, null),
                45 => array("to pit", 42, 0, null),
                46 => array("to unknown place/ not sure/DK", 42, 0, null),
                47 => array("to elsewhere", 42, 0, null),
                48 => array("Dry latrines", 29, 0, null),
                49 => array("Improved latrines", 48, 0, null),
                50 => array("Ventilated Improved Pit latrine", 49, 1, array(58, 66)),
                51 => array("Pit latrine with slab/covered latrine", 49, 1, array(59, 67)),
                52 => array("Traditional latrine", 48, 1, array(60, 68)),
                53 => array("Pit latrine without slab/open pit", 48, 1, array(61, 69)),
                54 => array("Hanging toilet/hanging latrine", 48, 1, array(62, 70)),
                55 => array("Bucket latrine", 48, 1, array(63, 71)),
                56 => array("Other", 48, 1, array(64, 72)),
                57 => array("Private Latrines", 48, 0, null),
                58 => array("Ventilated Improved Pit latrine", 57, 0, null),
                59 => array("Pit latrine with slab/covered latrine", 57, 0, null),
                60 => array("Traditional latrine", 57, 0, null),
                61 => array("Pit latrine without slab/open pit", 57, 0, null),
                62 => array("Hanging toilet/hanging latrine", 57, 0, null),
                63 => array("Bucket latrine", 57, 0, null),
                64 => array("Other", 57, 0, null),
                65 => array("Public/shared Latrines", 48, 0, null),
                66 => array("Ventilated Improved Pit latrine", 65, 0, null),
                67 => array("Pit latrine with slab/covered latrine", 65, 0, null),
                68 => array("Traditional latrine", 65, 0, null),
                69 => array("Pit latrine without slab/open pit", 65, 0, null),
                70 => array("Hanging toilet/hanging latrine", 65, 0, null),
                71 => array("Bucket latrine", 65, 0, null),
                72 => array("Other", 65, 0, null),
                73 => array("Composting toilets", 4, 0, null),
                74 => array("Composting toilet (private)", 73, 0, null),
                75 => array("Composting toilet (shared)", 73, 0, null),
                76 => array("Other improved", 4, 0, null),
                77 => array("Other 1", 76, 0, null),
                78 => array("Other 2", 76, 0, null),
                79 => array("No facility, bush, field ", 4, 0, null),
                80 => array("Other unimproved", 4, 0, null),
                81 => array("Other 1", 80, 0, null),
                82 => array("Other 2", 80, 0, null),
                83 => array("DK/missing information", 4, 0, null),
            ),
            'replacements' => array(
                // For sanitation, we need to modify the filter tree to introduce a new branch
                999 => array("Other flush and pour flush", 5, 0, null),
                6 => array("to piped sewer system", 999, 0, null),
                7 => array("to septic tank", 999, 0, null),
                8 => array("to pit", 999, 0, null),
                9 => array("to unknown place/ not sure/DK", 999, 0, null),
                10 => array("to elsewhere", 999, 0, null),
            ),
            'questionnaireUsages' => array(
                'Calculation' => array(85, 86, 87),
                'Estimate' => array(88, 89, 90, 91, 92, 93),
                'Ratio' => array(100, 101, 102, 103, 104, 105, 106, 107),
            ),
            'ratios' => array(
                'min' => 100,
                'max' => 107,
            ),
            'filterSet' => array(
                'improvedName' => 'Sanitation: use of improved facilities (JMP data)',
                'improvedUnimprovedName' => 'Sanitation: use of improved and unimproved facilities (JMP data)',
            ),
            'highFilters' => array(
                "Improved + shared" => array(
                    'row' => 94,
                    'children' => array(-6, -7, -8, -9, 49, 73, 76),
                    'excludes' => 96,
                    'isImproved' => true,
                    'formulas' => array(
                    ),
                ),
                "Sewerage connections" => array(
                    'row' => 95,
                    'children' => array(6),
                    'excludes' => 97,
                    'isImproved' => false,
                    'formulas' => array(
                    ),
                ),
                "Improved" => array(
                    'row' => null,
                    'children' => array(), // based on ratio
                    'excludes' => null,
                    'isImproved' => true,
                    'formulas' => array(
                        // Additionnal rules for developed countries
                        array(3, "=IF(AND(Improved + shared > 0.995, COUNT({Shared,Q#all}) = 0), Improved + shared, {self})", true),
                        array(4, "=IF(AND(Improved + shared > 0.995, COUNT({Shared,Q#all}) = 0), Improved + shared, {self})", true),
                        // Normal rules
                        array(3, "=IF(AND(ISNUMBER(Improved + shared), COUNT({Shared,Q#all}) > 0), Improved + shared * (1 - AVERAGE({Shared,Q#all})), NULL)"),
                        array(4, "=IF(AND(ISNUMBER(Improved + shared), COUNT({Shared,Q#all}) > 0), Improved + shared * (1 - AVERAGE({Shared,Q#all})), NULL)"),
                        array(5, "=IF(AND(ISNUMBER(Improved + shared), COUNT({Shared,Q#all}) > 0), Improved + shared * (1 - AVERAGE({Shared,Q#all})), NULL)"),
                    ),
                ),
                "Shared" => array(
                    'row' => null,
                    'children' => array(), // based on ratio
                    'excludes' => null, // Because Shared is a very special case, we totally ignore exclude rules
                    'isImproved' => false,
                    'formulas' => array(
                        array(3, '=IF(AND(ISNUMBER(Improved + shared), ISNUMBER(Improved)), Improved + shared - Improved, NULL)'),
                        array(4, '=IF(AND(ISNUMBER(Improved + shared), ISNUMBER(Improved)), Improved + shared - Improved, NULL)'),
                        array(5, '=IF(AND(ISNUMBER(Improved + shared), ISNUMBER(Improved)), Improved + shared - Improved, NULL)'),
                    ),
                ),
                "Other unimproved" => array(
                    'row' => null,
                    'children' => array(-10, 30, 52, 53, 54, 55, 56, 80),
                    'excludes' => null,
                    'isImproved' => false,
                    'formulas' => array(
                        array(3, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared = 1, 0, 1 - Improved + shared - Open defecation), NULL)'),
                        array(4, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared = 1, 0, 1 - Improved + shared - Open defecation), NULL)'),
                        array(5, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared + Open defecation >= 1, 0, IF(Improved + shared = 1, 0, 1 - Improved + shared - Open defecation)), NULL)'),
                    ),
                ),
                "Open defecation" => array(
                    'row' => null,
                    'children' => array(79),
                    'excludes' => 98,
                    'isImproved' => false,
                    'formulas' => array(
                        array(3, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared >= 0.995, 0, {self}), NULL)'),
                        array(4, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared >= 0.995, 0, {self}), NULL)'),
                        array(5, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared = 1, 0, {self}), NULL)'),
                    ),
                ),
            ),
        ),
    );
    private $defaultJustification = 'Imported from country files';
    private $partOffsets = array();
    private $cacheAlternateFilters = array();
    private $cacheFilters = array();
    private $cacheQuestions = array();
    private $cacheHighFilters = array();
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

            // Import all questionnaire found, until no questionnaire code found
            $col = 0;
            $this->colToParts = array();
            while ($this->importQuestionnaire($sheet, $col)) {
                $col += 6;
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

    protected function getFilter($definition)
    {
        $name = $definition[0];

        $parent = @$this->cacheFilters[$definition[1]];
        $parentName = $parent ? $parent->getName() : null;

        $filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');
        $filter = $filterRepository->getOneOfficialByNames($name, $parentName);
        if (!$filter) {

            $filter = new Filter();
            $this->getEntityManager()->persist($filter);
            $filter->setName($name);
            if ($parent) {
                $parent->addChild($filter);
            }
        }

        return $filter;
    }

    /**
     * Import official filters
     * @param array $officialFilters
     */
    protected function importOfficialFilters(array $officialFilters)
    {
        // Import filters
        $this->cacheFilters = array();
        $this->cacheQuestions = array();
        foreach ($officialFilters['definitions'] as $row => $definition) {
            $filter = $this->getFilter($definition);
            $this->cacheFilters[$row] = $filter;
        }

        // Add all summands to filters
        foreach ($officialFilters['definitions'] as $row => $definition) {
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
        foreach ($officialFilters['replacements'] as $row => $definition) {
            $replacementFilter = $this->getFilter($definition);
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
        foreach ($officialFilters['definitions'] as $row => $definition) {
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

    /**
     * Import a questionnaire from the given column offset.
     * Questionnaire and Answers will always be created new. All other objects will be retrieved from database if available.
     * @param \PHPExcel_Worksheet $sheet
     * @param integer $col
     * @return boolean whether it imported something
     */
    protected function importQuestionnaire(\PHPExcel_Worksheet $sheet, $col)
    {
        $code = trim($sheet->getCellByColumnAndRow($col + 2, 1)->getCalculatedValue());

        // If no code found, we assume no survey at all
        if (!$code) {
            return false;
        }

        // Sometimes code use 4-digit year instead of 2-digit, we replace it here
        // so we can find existing survey. This is the case of Burkina Faso, survey ENA2010
        $year = $sheet->getCellByColumnAndRow($col + 3, 3)->getCalculatedValue();
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
        if (isset($codeMapping[$code]))
            $code = $codeMapping[$code];

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

                return true;
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

            return true;
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

        return true;
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
            'Syrian Arab Republic' => 'Syria',
            'Occupied Palestinian Territory' => 'Palestinian Territory',
            'Palestine' => 'Palestinian Territory',
            "Dem. People's Republic of Korea" => 'North Korea',
            'Republic of Korea' => 'South Korea',
            'Iran (Islamic Republic of)' => 'Iran',
            "Lao People's Democratic Republic" => 'Laos',
            'Viet Nam' => 'Vietnam',
            'Timor-Leste' => 'East Timor',
            'United States Virgin Islands' => 'U.S. Virgin Islands',
            'St. Vincent and Grenadines' => 'Saint Vincent and the Grenadines',
            'St Vincent & grenadines' => 'Saint Vincent and the Grenadines',
            'Bolivia (Plurinational State of)' => 'Bolivia',
            'Venezuela (Bolivarian Republic of)' => 'Venezuela',
            "Côte d'Ivoire" => 'Ivory Coast',
            'United Republic of Tanzania' => 'Tanzania',
            'Libyan Arab Jamahiriya' => 'Libya',
            'Congo' => 'Republic of the Congo',
            'Russian Federation' => 'Russia',
            'Republic of Moldova' => 'Moldova',
            'TFYR Macedonia' => 'Macedonia',
            'United States of America' => 'United States',
            // Unusual spelling
            'Antigua & Barbuda' => 'Antigua and Barbuda',
            'Afganistan' => 'Afghanistan',
            'Dominican Rep' => 'Dominican Republic',
            'Micronesia (Fed. States of)' => 'Micronesia',
            'Guinée' => 'Guinea',
            'Senagal' => 'Senegal',
            'Cap Verde' => 'Cape Verde',
            'Congo DR' => 'Democratic Republic of the Congo',
            'Bosnia' => 'Bosnia and Herzegovina',
            // Case mistake
            'ANGOLA' => 'Angola',
            'Saint lucia' => 'Saint Lucia',
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
     * Returns an alternate filter linked to the official either from database, or newly created
     * @param Questionnaire $questionnaire
     * @param string $name
     * @param Filter $officialFilter
     * @return Filter
     */
    protected function getAlternateFilter(Questionnaire $questionnaire, $name, Filter $officialFilter)
    {
        if ($name == $officialFilter->getName())
            return $officialFilter;

        $key = \Application\Utility::getCacheKey(func_get_args());
        if (array_key_exists($key, $this->cacheAlternateFilters)) {
            $filter = $this->cacheAlternateFilters[$key];
        } else {
            $this->getEntityManager()->flush();
            $filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');
            $criteria = array(
                'name' => $name,
                'officialFilter' => $officialFilter,
                'questionnaire' => $questionnaire,
            );
            $filter = $filterRepository->findOneBy($criteria);
        }

        if (!$filter) {
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
        $questionnaireUsageRepository = $this->getEntityManager()->getRepository('Application\Model\Rule\QuestionnaireUsage');
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

        // Some formulas (usually estimations) hardcode values as percent between 0 - 100, we need to convert them
        // to 0.00 - 1.00, but only for hardcoded values and not any number within more complex formula (it would be too dangerous)
        // eg: "=29.6" => "=0.296"
        // This is the case for Cambodge DHS05 "Bottled water with HC" estimation
        $ruleRows = $this->definitions[$sheet->getTitle()]['questionnaireUsages'];
        if (in_array($row, $ruleRows['Estimate']) || in_array($row, $ruleRows['Calculation'])) {
            $replacedFormula = \Application\Utility::pregReplaceUniqueCallback('/^=(-?\d+(\.\d+)?)$/', function($matches) {
                                $number = $matches[1];
                                $number = $number / 100;

                                return "=$number";
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
     * Import high filters, their FilterSet
     * @param array $filterSetNames
     * @param array $filters
     */
    protected function importHighFilters(array $filterSetNames, array $filters)
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
                $this->getEntityManager()->persist($highFilter);

                if ($filterData['isImproved']) {
                    $improvedFilterSet->addFilter($highFilter);
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
     * Finish high filters, by importing their exclude rules and their formulas (if any)
     * @param array $filters
     * @param \PHPExcel_Worksheet $sheet
     */
    protected function finishHighFilters(array $filters, \PHPExcel_Worksheet $sheet)
    {
        $complementaryTotalFormula = $this->getRule('Total part is sum of parts if both are available', '=IF(AND(ISNUMBER({F#current,Q#current,P#' . $this->partRural->getId() . '}), ISNUMBER({F#current,Q#current,P#' . $this->partUrban->getId() . '})), ({F#current,Q#current,P#' . $this->partRural->getId() . '} * {Q#current,P#' . $this->partRural->getId() . '} + {F#current,Q#current,P#' . $this->partUrban->getId() . '} * {Q#current,P#' . $this->partUrban->getId() . '}) / {Q#current,P#' . $this->partTotal->getId() . '}, {self})');
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
        $repository = $this->getEntityManager()->getRepository('Application\Model\Rule\FilterQuestionnaireUsage');

        $this->getEntityManager()->flush();
        $filterQuestionnaireUsage = $repository->findOneBy(array(
            'questionnaire' => $questionnaire,
            'rule' => $rule,
            'part' => $part,
            'filter' => $filter,
            'isSecondLevel' => $isSecondLevel,
        ));

        if (!$filterQuestionnaireUsage) {
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
            if ($includedValue != 'Yes') {
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
