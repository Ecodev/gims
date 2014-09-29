<?php

namespace Application\Service\Importer;

use Application\Model\Filter;

abstract class AbstractImporter
{

    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

use \Application\Traits\EntityManagerAware;

    /**
     * sheet name =>
     *      defintions =>
     *          row => name, parent row, computing type, summands rows
     * @var array
     */
    protected $definitions = array(
        'Tables_W' => array(
            'definitions' => array(
                3 => array("JMP", null, 0, null),
                4 => array("Water", 3, 0, null),
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
                55 => array("Rainwater into covered cistern/tank", 54, 0, null),
                56 => array("Uncovered cistern/tank", 54, 0, null),
                57 => array("Bottled water", 4, 0, null),
                58 => array("Bottled water with other improved", 57, 0, null),
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
            'cellReplacements' => array(
                14 => 'IF(ISNUMBER(A14), A14, SUM(A26, A34, A46))',
                15 => 'IF(ISNUMBER(A15), A15, SUM(A27, A35, A47))',
                16 => 'IF(ISNUMBER(A16), A16, SUM(A28, A36, A48))',
                17 => 'IF(ISNUMBER(A17), A17, SUM(A29, A37, A49))',
                18 => 'IF(ISNUMBER(A18), A18, SUM(A38, A50))',
                19 => 'IF(ISNUMBER(A19), A19, SUM(A39, A51))',
                20 => 'IF(ISNUMBER(A20), A20, SUM(A40, A52))',
                21 => 'IF(ISNUMBER(A21), A21, SUM(A41, A53))',
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
                    'thematic' => 4,
                    'children' => array(5, 12, 55, 58, 68),
                    'excludes' => 91,
                    'isImproved' => true,
                    'regressionRules' => array(
                        'default' => array(
                            array(5, '=IF(AND(ISNUMBER(Total improvedURBAN), ISNUMBER(Total improvedRURAL)), (Total improvedURBAN * POPULATION_URBAN + Total improvedRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL)'),
                        ),
                        'onlyTotal' => array(
                            array(3, '=Total improvedTOTAL'),
                            array(4, '=Total improvedTOTAL'),
                        ),
                        'onlyRural' => array(
                            array(5, '=Total improvedRURAL'),
                        ),
                        'onlyUrban' => array(
                            array(5, '=Total improvedURBAN'),
                        ),
                        'Saudi Arabia' => array(
                            array(3, '=Total improvedTOTAL'),
                            array(4, '=Total improvedTOTAL'),
                        ),
                    ),
                ),
                "Piped onto premises" => array(
                    'row' => 90,
                    'thematic' => 4,
                    'children' => array(6),
                    'excludes' => 92,
                    'isImproved' => true,
                    'regressionRules' => array(
                        'default' => array(
                            array(5, '=IF(AND(ISNUMBER(Piped onto premisesURBAN), ISNUMBER(Piped onto premisesRURAL)), (Piped onto premisesURBAN * POPULATION_URBAN + Piped onto premisesRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL)'),
                        ),
                        'onlyTotal' => array(
                            array(3, '=Piped onto premisesTOTAL'),
                            array(4, '=Piped onto premisesTOTAL'),
                        ),
                        'onlyRural' => array(
                            array(5, '=Piped onto premisesRURAL'),
                        ),
                        'onlyUrban' => array(
                            array(5, '=Piped onto premisesURBAN'),
                        ),
                        'popHigherThanTotal' => array(
                            array(3, '=IF(AND(ISNUMBER(Total improved), {self} > Total improved), Total improved, IF(AND(ISNUMBER(Total improved), NOT(ISNUMBER({self}))), Piped onto premisesLATER, {self}))'),
                            array(4, '=IF(AND(ISNUMBER(Total improved), {self} > Total improved), Total improved, IF(AND(ISNUMBER(Total improved), NOT(ISNUMBER({self}))), Piped onto premisesLATER, {self}))'),
                            array(5, '=IF(AND(ISNUMBER(Piped onto premisesURBAN), ISNUMBER(Piped onto premisesRURAL)), (Piped onto premisesURBAN * POPULATION_URBAN + Piped onto premisesRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL)'),
                        ),
                        'Tunisia' => array(
                            array(3, '=IF(ISNUMBER({self}), {self}, Piped onto premisesEARLIER)'),
                            array(5, '=IF(AND(ISNUMBER(Piped onto premisesURBAN), ISNUMBER(Piped onto premisesRURAL)), (Piped onto premisesURBAN * POPULATION_URBAN + Piped onto premisesRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL)'),
                        ),
                        'Saudi Arabia' => array(
                            array(3, '=Piped onto premisesTOTAL'),
                            array(4, '=Piped onto premisesTOTAL'),
                        ),
                    ),
                ),
                "Surface water" => array(
                    'row' => 87,
                    'thematic' => 4,
                    'children' => array(60),
                    'excludes' => 93,
                    'isImproved' => false,
                    'regressionRules' => array(
                        'default' => array(
                            array(3, '=IF(ISNUMBER(Total improved), IF(Total improved >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'),
                            array(4, '=IF(ISNUMBER(Total improved), IF(Total improved >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'),
                            array(5, '=IF(Total improved = 100%, 0%, IF(AND(ISNUMBER(Surface waterURBAN), ISNUMBER(Surface waterRURAL)), (Surface waterURBAN * POPULATION_URBAN + Surface waterRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL))'),
                        ),
                        'onlyTotal' => array(
                            array(3, '=Surface waterTOTAL'),
                            array(4, '=Surface waterTOTAL'),
                            array(5, '=IF(Total improved = 100%, 0%, {self})'),
                        ),
                        'onlyRural' => array(
                            array(4, '=IF(ISNUMBER(Total improved), IF(Total improved >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'),
                            array(5, '=Surface waterRURAL'),
                        ),
                        'onlyUrban' => array(
                            array(3, '=IF(ISNUMBER(Total improved), IF(Total improved >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'),
                            array(5, '=Surface waterURBAN'),
                        ),
                        'Belarus' => array(
                            array(3, '=IF(ISNUMBER(Total improved), IF(Total improved >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'),
                            array(4, '=IF(ISNUMBER(Total improved), IF(Total improved >= 99%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'),
                            array(5, '=IF(Total improved = 100%, 0%, IF(AND(ISNUMBER(Surface waterURBAN), ISNUMBER(Surface waterRURAL)), (Surface waterURBAN * POPULATION_URBAN + Surface waterRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL))'),
                        ),
                        'TFYR Macedonia' => array(
                            array(3, '=IF(ISNUMBER(Total improved), IF(Total improved >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'),
                            array(4, '=Surface waterURBAN'),
                            array(5, '=IF(Total improved = 100%, 0%, IF(AND(ISNUMBER(Surface waterURBAN), ISNUMBER(Surface waterRURAL)), (Surface waterURBAN * POPULATION_URBAN + Surface waterRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL))'),
                        ),
                        'United States of America' => array(
                            array(3, '=IF(ISNUMBER(Total improved), IF(Total improved >= 99.3%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'),
                            array(4, '=IF(ISNUMBER(Total improved), IF(Total improved >= 93.6%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'),
                            array(5, '=IF(Total improved = 100%, 0%, IF(AND(ISNUMBER(Surface waterURBAN), ISNUMBER(Surface waterRURAL)), (Surface waterURBAN * POPULATION_URBAN + Surface waterRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL))'),
                        ),
                        'Saudi Arabia' => array(
                            array(3, '=Surface waterTOTAL'),
                            array(4, '=Surface waterTOTAL'),
                            array(5, '=IF(Total improved = 100%, 0%, {self})'),
                        ),
                    ),
                ),
                "Other Improved" => array(
                    'row' => null,
                    'thematic' => 4,
                    'children' => array(9, 10, 12, 55, 58, 68),
                    'excludes' => null,
                    'isImproved' => false,
                    'rule' => '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)',
                    'regressionRules' => array(
                        'default' => array(
                            array(3, '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)'),
                            array(4, '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)'),
                            array(5, '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)'),
                        ),
                        'onlyTotal' => array(
                            array(3, '=Other ImprovedTOTAL'),
                            array(4, '=Other ImprovedTOTAL'),
                            array(5, '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)'),
                        ),
                        'onlyRural' => array(
                            array(4, '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)'),
                            array(5, '=Other ImprovedRURAL'),
                        ),
                        'onlyUrban' => array(
                            array(3, '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)'),
                            array(5, '=Other ImprovedURBAN'),
                        ),
                        'Saudi Arabia' => array(
                            array(3, '=Other ImprovedTOTAL'),
                            array(4, '=Other ImprovedTOTAL'),
                            array(5, '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)'),
                        ),
                    ),
                ),
                "Other Unimproved" => array(
                    'row' => null,
                    'thematic' => 4,
                    'children' => array(56, 59, 71),
                    'excludes' => null,
                    'isImproved' => false,
                    'rule' => '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Surface water)), 100% - Total improved - Surface water, NULL)',
                    'regressionRules' => array(
                        'default' => array(
                            array(3, '=IF(ISNUMBER(Total improved), IF(Total improved = 100%, 0%, 100% - Total improved - Surface water), NULL)'),
                            array(4, '=IF(ISNUMBER(Total improved), IF(Total improved = 100%, 0%, 100% - Total improved - Surface water), NULL)'),
                            array(5, '=IF(ISNUMBER(Total improved), IF(Total improved = 100%, 0%, 100% - Total improved - Surface water), NULL)'),
                        ),
                        'onlyTotal' => array(
                            array(3, '=Other UnimprovedTOTAL'),
                            array(4, '=Other UnimprovedTOTAL'),
                            array(5, '=IF(ISNUMBER(Total improved), IF(Total improved = 100%, 0%, 100% - Total improved - Surface water), NULL)'),
                        ),
                        'onlyRural' => array(
                            array(4, '=IF(ISNUMBER(Total improved), IF(Total improved = 100%, 0%, 100% - Total improved - Surface water), NULL)'),
                            array(5, '=Other UnimprovedRURAL'),
                        ),
                        'onlyUrban' => array(
                            array(3, '=IF(ISNUMBER(Total improved), IF(Total improved = 100%, 0%, 100% - Total improved - Surface water), NULL)'),
                            array(5, '=Other UnimprovedURBAN'),
                        ),
                        'Saudi Arabia' => array(
                            array(3, '=Other UnimprovedTOTAL'),
                            array(4, '=Other UnimprovedTOTAL'),
                            array(5, '=IF(ISNUMBER(Total improved), IF(Total improved = 100%, 0%, 100% - Total improved - Surface water), NULL)'),
                        ),
                    ),
                ),
            ),
            'extras' => [
                104 => '[Water] Number of people covered by the questionnaire',
                105 => '[Water] Number of households covered by the questionnaire',
                106 => '[Water] Total population',
            ],
        ),
        'Tables_S' => array(
            'definitions' => array(
                3 => array("JMP", null, 0, null),
                4 => array("Sanitation", 3, 0, null),
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
            'cellReplacements' => array(
            // No cell replacements to make for sanitation
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
                    'thematic' => 4,
                    'children' => array(-6, -7, -8, -9, 49, 73, 76),
                    'excludes' => 96,
                    'isImproved' => true,
                    'regressionRules' => array(
                        'default' => array(
                            array(5, '=IF(ISNUMBER(Shared), Shared + Improved, IF(AND(ISNUMBER(Improved + sharedURBAN), ISNUMBER(Improved + sharedRURAL)), (Improved + sharedURBAN * POPULATION_URBAN + Improved + sharedRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL))'),
                        ),
                        'onlyTotal' => array(
                            array(3, '=Improved + sharedTOTAL'),
                            array(4, '=Improved + sharedTOTAL'),
                        ),
                        'onlyRural' => array(
                            array(5, '=Improved + sharedRURAL'),
                        ),
                        'onlyUrban' => array(
                            array(5, '=Improved + sharedURBAN'),
                        ),
                        'Saudi Arabia' => array(
                            array(3, '=Improved + sharedTOTAL'),
                            array(4, '=Improved + sharedTOTAL'),
                        ),
                    ),
                ),
                "Sewerage connections" => array(
                    'row' => 95,
                    'thematic' => 4,
                    'children' => array(6),
                    'excludes' => 97,
                    'isImproved' => false,
                    'regressionRules' => array(
                        'default' => array(
                        ),
                        'onlyTotal' => array(
                            array(3, '=Sewerage connectionsTOTAL'),
                            array(4, '=Sewerage connectionsTOTAL'),
                        ),
                        'onlyRural' => array(
                            array(5, '=Sewerage connectionsRURAL'),
                        ),
                        'onlyUrban' => array(
                            array(5, '=Sewerage connectionsURBAN'),
                        ),
                        'Saudi Arabia' => array(
                            array(3, '=Sewerage connectionsTOTAL'),
                            array(4, '=Sewerage connectionsTOTAL'),
                        ),
                    ),
                ),
                "Improved" => array(
                    'row' => null,
                    'thematic' => 4,
                    'children' => array(), // based on ratio
                    'excludes' => null,
                    'isImproved' => true,
                    'regressionRules' => array(
                        'default' => array(
                            // Additionnal rules for developed countries
                            array(3, "=IF(AND(Improved + shared > 99.5%, COUNT(ALL_RATIOS) = 0), Improved + shared, {self})", true),
                            array(4, "=IF(AND(Improved + shared > 99.5%, COUNT(ALL_RATIOS) = 0), Improved + shared, {self})", true),
                            // Normal rules
                            array(3, "=IF(AND(ISNUMBER(Improved + shared), COUNT(ALL_RATIOS) > 0), Improved + shared * (100% - AVERAGE(ALL_RATIOS)), NULL)"),
                            array(4, "=IF(AND(ISNUMBER(Improved + shared), COUNT(ALL_RATIOS) > 0), Improved + shared * (100% - AVERAGE(ALL_RATIOS)), NULL)"),
                            array(5, "=IF(AND(ISNUMBER(ImprovedURBAN), ISNUMBER(ImprovedRURAL)), (ImprovedURBAN * POPULATION_URBAN + ImprovedRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL)"),
                        ),
                        'onlyTotal' => array(
                            array(3, '=ImprovedTOTAL'),
                            array(4, '=ImprovedTOTAL'),
                            array(5, "=IF(AND(ISNUMBER(Improved + shared), COUNT(ALL_RATIOS) > 0), Improved + shared * (100% - AVERAGE(ALL_RATIOS)), NULL)"),
                        ),
                        'onlyRural' => array(
                            // Additionnal rules for developed countries
                            array(4, "=IF(AND(Improved + shared > 99.5%, COUNT(ALL_RATIOS) = 0), Improved + shared, {self})", true),
                            // Normal rules
                            array(4, "=IF(AND(ISNUMBER(Improved + shared), COUNT(ALL_RATIOS) > 0), Improved + shared * (100% - AVERAGE(ALL_RATIOS)), NULL)"),
                            array(5, '=ImprovedRURAL'),
                        ),
                        'onlyUrban' => array(
                            // Additionnal rules for developed countries
                            array(3, "=IF(AND(Improved + shared > 99.5%, COUNT(ALL_RATIOS) = 0), Improved + shared, {self})", true),
                            // Normal rules
                            array(3, "=IF(AND(ISNUMBER(Improved + shared), COUNT(ALL_RATIOS) > 0), Improved + shared * (100% - AVERAGE(ALL_RATIOS)), NULL)"),
                            array(5, '=ImprovedURBAN'),
                        ),
                        'United States of America' => array(
                            // Additionnal rules for developed countries
                            array(3, "=IF(AND(Improved + shared > 99.5%, COUNT(ALL_RATIOS) = 0), Improved + shared, {self})", true),
                            array(4, "=IF(AND(Improved + shared > 98.4%, COUNT(ALL_RATIOS) = 0), Improved + shared, {self})", true),
                            // Normal rules
                            array(3, "=IF(AND(ISNUMBER(Improved + shared), COUNT(ALL_RATIOS) > 0), Improved + shared * (100% - AVERAGE(ALL_RATIOS)), NULL)"),
                            array(4, "=IF(AND(ISNUMBER(Improved + shared), COUNT(ALL_RATIOS) > 0), Improved + shared * (100% - AVERAGE(ALL_RATIOS)), NULL)"),
                            array(5, "=IF(AND(ISNUMBER(ImprovedURBAN), ISNUMBER(ImprovedRURAL)), (ImprovedURBAN * POPULATION_URBAN + ImprovedRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL)"),
                        ),
                        'Saudi Arabia' => array(
                            array(3, '=ImprovedTOTAL'),
                            array(4, '=ImprovedTOTAL'),
                            array(5, "=IF(AND(ISNUMBER(Improved + shared), COUNT(ALL_RATIOS) > 0), Improved + shared * (100% - AVERAGE(ALL_RATIOS)), NULL)"),
                        ),
                    ),
                ),
                "Shared" => array(
                    'row' => null,
                    'thematic' => 4,
                    'children' => array(), // based on ratio
                    'excludes' => null,
                    'isImproved' => false,
                    'regressionRules' => array(
                        'default' => array(
                            array(3, '=IF(AND(ISNUMBER(Improved + shared), ISNUMBER(Improved)), Improved + shared - Improved, NULL)'),
                            array(4, '=IF(AND(ISNUMBER(Improved + shared), ISNUMBER(Improved)), Improved + shared - Improved, NULL)'),
                            array(5, "=IF(AND(ISNUMBER(SharedURBAN), ISNUMBER(SharedRURAL)), (SharedURBAN * POPULATION_URBAN + SharedRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL)"),
                        ),
                        'onlyTotal' => array(
                            array(3, '=SharedTOTAL'),
                            array(4, '=SharedTOTAL'),
                            array(5, "=IF(AND(ISNUMBER(Improved + shared), ISNUMBER(Improved)), Improved + shared - Improved, NULL)"),
                        ),
                        'onlyRural' => array(
                            array(4, '=IF(AND(ISNUMBER(Improved + shared), ISNUMBER(Improved)), Improved + shared - Improved, NULL)'),
                            array(5, '=SharedRURAL'),
                        ),
                        'onlyUrban' => array(
                            array(3, '=IF(AND(ISNUMBER(Improved + shared), ISNUMBER(Improved)), Improved + shared - Improved, NULL)'),
                            array(5, '=SharedURBAN'),
                        ),
                        'Saudi Arabia' => array(
                            array(3, '=SharedTOTAL'),
                            array(4, '=SharedTOTAL'),
                            array(5, "=IF(AND(ISNUMBER(Improved + shared), ISNUMBER(Improved)), Improved + shared - Improved, NULL)"),
                        ),
                    ),
                ),
                "Other unimproved" => array(
                    'row' => null,
                    'thematic' => 4,
                    'children' => array(80),
                    'excludes' => null,
                    'isImproved' => false,
                    'rule' => '=IF(AND(ISNUMBER(Improved + shared), ISNUMBER(Open defecation)), 100% - Improved + shared - Open defecation, NULL)',
                    'regressionRules' => array(
                        'default' => array(
                            array(3, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared = 100%, 0%, 100% - Improved + shared - Open defecation), NULL)'),
                            array(4, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared = 100%, 0%, 100% - Improved + shared - Open defecation), NULL)'),
                            array(5, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared + Open defecation >= 100%, 0%, IF(Improved + shared = 100%, 0%, 100% - Improved + shared - Open defecation)), NULL)'),
                        ),
                        'onlyTotal' => array(
                            array(3, '=Other unimprovedTOTAL'),
                            array(4, '=Other unimprovedTOTAL'),
                            array(5, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared + Open defecation >= 100%, 0%, IF(Improved + shared = 100%, 0%, 100% - Improved + shared - Open defecation)), NULL)'),
                        ),
                        'onlyRural' => array(
                            array(4, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared = 100%, 0%, 100% - Improved + shared - Open defecation), NULL)'),
                            array(5, '=Other unimprovedRURAL'),
                        ),
                        'onlyUrban' => array(
                            array(3, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared = 100%, 0%, 100% - Improved + shared - Open defecation), NULL)'),
                            array(5, '=Other unimprovedURBAN'),
                        ),
                        'Saudi Arabia' => array(
                            array(3, '=Other unimprovedTOTAL'),
                            array(4, '=Other unimprovedTOTAL'),
                            array(5, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared + Open defecation >= 100%, 0%, IF(Improved + shared = 100%, 0%, 100% - Improved + shared - Open defecation)), NULL)'),
                        ),
                    ),
                ),
                "Open defecation" => array(
                    'row' => null,
                    'thematic' => 4,
                    'children' => array(79),
                    'excludes' => 98,
                    'isImproved' => false,
                    'regressionRules' => array(
                        'default' => array(
                            array(3, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Open defecationLATER)), Open defecationLATER, IF(AND(ISNUMBER({self}), Improved + shared + {self} >= 100%), 100% - Improved + shared, {self}))), NULL)'),
                            array(4, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Open defecationLATER)), Open defecationLATER, IF(AND(ISNUMBER({self}), Improved + shared + {self} >= 100%), 100% - Improved + shared, {self}))), NULL)'),
                            array(5, '=IF(Improved + shared = 100%, 0%, IF(AND(ISNUMBER(Open defecationURBAN), ISNUMBER(Open defecationRURAL)), (Open defecationURBAN * POPULATION_URBAN + Open defecationRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL))'),
                        ),
                        'onlyTotal' => array(
                            array(3, '=Open defecationTOTAL'),
                            array(4, '=Open defecationTOTAL'),
                            array(5, '=IF(Improved + shared = 100%, 0%, {self})'),
                        ),
                        'onlyRural' => array(
                            array(4, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Open defecationLATER)), Open defecationLATER, IF(AND(ISNUMBER({self}), Improved + shared + {self} >= 100%), 100% - Improved + shared, {self}))), NULL)'),
                            array(5, '=Open defecationRURAL'),
                        ),
                        'onlyUrban' => array(
                            array(3, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Open defecationLATER)), Open defecationLATER, IF(AND(ISNUMBER({self}), Improved + shared + {self} >= 100%), 100% - Improved + shared, {self}))), NULL)'),
                            array(5, '=Open defecationURBAN'),
                        ),
                        'Tunisia' => array(
                            array(3, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Open defecationLATER)), Open defecationLATER, IF(AND(ISNUMBER({self}), Improved + shared + {self} >= 100%), 100% - Improved + shared, {self}))), NULL)'),
                            array(4, '=IF({Y} >= 2006, Open defecationEARLIER - 2%, IF(ISNUMBER(Improved + shared), IF(Improved + shared >= 99.5%, 0%, IF(AND(ISNUMBER({self}), Improved + shared + {self} >= 100%), 100% - Improved + shared, {self})), NULL))'),
                            array(5, '=IF(Improved + shared = 100%, 0%, IF(AND(ISNUMBER(Open defecationURBAN), ISNUMBER(Open defecationRURAL)), (Open defecationURBAN * POPULATION_URBAN + Open defecationRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL))'),
                        ),
                        'Saudi Arabia' => array(
                            array(3, '=Open defecationTOTAL'),
                            array(4, '=Open defecationTOTAL'),
                            array(5, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared >= 100%, 0%, IF(OR(AND(ISNUMBER({self}), Improved + shared + {self} >= 100%), NOT(ISNUMBER({self}))), 100% - Improved + shared, {self})), NULL)'),
                        ),
                    ),
                ),
            ),
            'extras' => [
                108 => '[Sanitation] Number of people covered by the questionnaire',
                109 => '[Sanitation] Number of households covered by the questionnaire',
                110 => '[Sanitation] Total population',
            ],
        ),
    );

    /**
     * Get or create a filter and link it to its parent
     * @param array $definition
     * @param array $cache
     * @return \Application\Model\Filter
     */
    protected function getFilter(array $definition, array $cache)
    {
        $name = $definition[0];

        $parent = @$cache[$definition[1]];
        $parentName = $parent ? $parent->getName() : null;

        $filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');
        $filter = $filterRepository->getOneByNames($name, $parentName);
        if (!$filter) {

            $filter = new Filter($name);
            $this->getEntityManager()->persist($filter);
            if ($parent) {
                $parent->addChild($filter);

                if ($parentName != 'JMP') {
                    $filter->setThematicFilter(@$cache[4]);
                } else {
                    $filter->setIsThematic(true);
                }
            }
        }

        return $filter;
    }

}
