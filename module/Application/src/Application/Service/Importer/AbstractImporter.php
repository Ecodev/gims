<?php

namespace Application\Service\Importer;

use Application\Model\Filter;

abstract class AbstractImporter
{

    use \Zend\ServiceManager\ServiceLocatorAwareTrait;
    use \Application\Traits\EntityManagerAware;

    protected $mainFilterBgColor = '';
    protected $secondaryWaterFiltersBgColor = '';
    protected $secondarySanitationFiltersBgColor = '';

    /**
     * sheet name =>
     *      defintions =>
     *          row => name, parent row, computing type, summands rows, extra summand, bg color
     * @var array
     */
    protected $definitions = [
        'Tables_W' => [
            'definitions' => [
                3 => ["JMP", null, 0, null],
                4 => ["Water", 3, 0, null],
                5 => ["Tap water", 4, 0, null],
                6 => ["House connections", 5, 0, null, null, "#ffff87"],
                7 => ["Piped water into dwelling", 6, 0, null, null, "#ffff87"],
                8 => ["Piped water to yard/plot", 6, 0, null, null, "#ffff87"],
                9 => ["Public tap, standpipe", 5, 0, null, null, "#c2ffff"],
                10 => ["Other", 5, 0, null, null, "#c2ffff"],
                11 => ["Ground water", 4, 0, null],
                12 => ["Protected ground water (all protected wells or springs)", 11, 1, [14, 26, 46, 34], null, "#c2ffff"],
                13 => ["Unprotected ground water (all unprotected wells or springs)", 11, 1, [18, 38, 50]],
                14 => ["Undefined protected wells or springs", 12, 2, null, null, "#c2ffff"],
                15 => ["Private", 14, 1, null, null, "#c2ffff"],
                16 => ["Public", 14, 1, null, null, "#c2ffff"],
                17 => ["Other", 14, 1, null, null, "#c2ffff"],
                18 => ["Undefined unprotected wells or springs", 13, 2, null],
                19 => ["Private", 18, 1, null],
                20 => ["Public", 18, 1, null],
                21 => ["Other", 18, 1, null],
                22 => ["All wells", 11, 0, null],
                23 => ["Private", 22, 1, [27, 31]],
                24 => ["Public", 22, 1, [28, 32]],
                25 => ["Other", 22, 1, [29, 33]],
                26 => ["Tubewell, borehole", 22, 0, null, null, "#c2ffff"],
                27 => ["Private", 26, 0, null, null, "#c2ffff"],
                28 => ["Public", 26, 0, null, null, "#c2ffff"],
                29 => ["Other", 26, 0, null, null, "#c2ffff"],
                30 => ["Traditional wells", 22, 0, null],
                31 => ["Private", 30, 1, [35, 39]],
                32 => ["Public", 30, 1, [36, 40]],
                33 => ["Other", 30, 1, [37, 41]],
                34 => ["Protected well", 30, 0, null, null, "#c2ffff"],
                35 => ["Private", 34, 0, null, null, "#c2ffff"],
                36 => ["Public", 34, 0, null, null, "#c2ffff"],
                37 => ["Other", 34, 0, null, null, "#c2ffff"],
                38 => ["Unprotected well", 30, 0, null],
                39 => ["Private", 38, 0, null],
                40 => ["Public", 38, 0, null],
                41 => ["Other", 38, 0, null],
                42 => ["All springs", 11, 0, null],
                43 => ["Private", 42, 1, [47, 51]],
                44 => ["Public", 42, 1, [48, 52]],
                45 => ["Other", 42, 1, [49, 53]],
                46 => ["Protected spring", 42, 0, null, null, "#c2ffff"],
                47 => ["Private", 46, 0, null, null, "#c2ffff"],
                48 => ["Public", 46, 0, null, null, "#c2ffff"],
                49 => ["Other", 46, 0, null, null, "#c2ffff"],
                50 => ["Unprotected spring", 42, 0, null],
                51 => ["Private", 50, 0, null],
                52 => ["Public", 50, 0, null],
                53 => ["Other", 50, 0, null],
                54 => ["Rainwater", 4, 0, null],
                55 => ["Rainwater into covered cistern/tank", 54, 0, null, null, "#c2ffff"],
                56 => ["Uncovered cistern/tank", 54, 0, null],
                57 => ["Bottled water", 4, 0, null],
                58 => ["Bottled water with other improved", 57, 0, null, null, "#c2ffff"],
                59 => ["without other improved", 57, 0, null],
                60 => ["Surface water", 4, 0, null, null, "#fb8654"],
                61 => ["River", 60, 0, null],
                62 => ["Lake", 60, 0, null],
                63 => ["Dam", 60, 0, null],
                64 => ["Pond", 60, 0, null],
                65 => ["Stream", 60, 0, null],
                66 => ["Irrigation channel", 60, 0, null],
                67 => ["Other", 60, 0, null],
                68 => ["Other improved sources", 4, 0, null],
                69 => ["Other 1", 68, 0, null, null, "#c2ffff"],
                70 => ["Other 2", 68, 0, null, null, "#c2ffff"],
                71 => ["Other non-improved", 4, 0, null],
                72 => ["Cart with small tank/drum", 71, 0, null],
                73 => ["Tanker truck provided", 71, 0, null],
                74 => ["Other 1", 71, 0, null],
                75 => ["Other 2", 71, 0, null],
                76 => ["DK/missing", 4, 0, null],
            ],
            'replacements' => [
            // No replacements to make for water
            ],
            'cellReplacements' => [
                14 => 'IF(ISNUMBER(A14), A14, SUM(A26, A34, A46))',
                15 => 'IF(ISNUMBER(A15), A15, SUM(A27, A35, A47))',
                16 => 'IF(ISNUMBER(A16), A16, SUM(A28, A36, A48))',
                17 => 'IF(ISNUMBER(A17), A17, SUM(A29, A37, A49))',
                18 => 'IF(ISNUMBER(A18), A18, SUM(A38, A50))',
                19 => 'IF(ISNUMBER(A19), A19, SUM(A39, A51))',
                20 => 'IF(ISNUMBER(A20), A20, SUM(A40, A52))',
                21 => 'IF(ISNUMBER(A21), A21, SUM(A41, A53))',
            ],
            'questionnaireUsages' => [
                'Thematic' => 4,
                'Groups' => [
                    'Calculation' => [78, 79, 80, 81, 82],
                    'Estimate' => [83, 84, 85, 86, 87, 88],
                    'Ratio' => [94, 95, 96, 97, 98, 99, 100, 101, 102, 103],
                ],
            ],
            'filterSet' => [
                'improvedName' => 'Water: use of improved sources (JMP data)',
                'improvedUnimprovedName' => 'Water: use of improved and unimproved sources (JMP data)',
            ],
            'highFilters' => [
                "Total improved" => [
                    'row' => 89,
                    'thematic' => 4,
                    'children' => [5, 12, 55, 58, 68],
                    'excludes' => 91,
                    'sorting' => 100,
                    'color' => "#094aff",
                    'isImproved' => true,
                    'regressionRules' => [
                        'default' => [
                            [5, '=IF(AND(ISNUMBER(Total improvedURBAN), ISNUMBER(Total improvedRURAL)), (Total improvedURBAN * POPULATION_URBAN + Total improvedRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL)'],
                        ],
                        'onlyTotal' => [
                            [3, '=Total improvedTOTAL'],
                            [4, '=Total improvedTOTAL'],
                        ],
                        'onlyRural' => [
                            [5, '=Total improvedRURAL'],
                        ],
                        'onlyUrban' => [
                            [5, '=Total improvedURBAN'],
                        ],
                        'Saudi Arabia' => [
                            [3, '=Total improvedTOTAL'],
                            [4, '=Total improvedTOTAL'],
                        ],
                    ],
                ],
                "Piped onto premises" => [
                    'row' => 90,
                    'thematic' => 4,
                    'children' => [6],
                    'excludes' => 92,
                    'sorting' => 105,
                    'color' => "#1270c0",
                    'isImproved' => true,
                    'regressionRules' => [
                        'default' => [
                            [5, '=IF(AND(ISNUMBER(Piped onto premisesURBAN), ISNUMBER(Piped onto premisesRURAL)), (Piped onto premisesURBAN * POPULATION_URBAN + Piped onto premisesRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL)'],
                        ],
                        'onlyTotal' => [
                            [3, '=Piped onto premisesTOTAL'],
                            [4, '=Piped onto premisesTOTAL'],
                        ],
                        'onlyRural' => [
                            [5, '=Piped onto premisesRURAL'],
                        ],
                        'onlyUrban' => [
                            [5, '=Piped onto premisesURBAN'],
                        ],
                        'popHigherThanTotal' => [
                            [3, '=IF(AND(ISNUMBER(Total improved), {self} > Total improved), Total improved, IF(AND(ISNUMBER(Total improved), NOT(ISNUMBER({self}))), Piped onto premisesLATER, {self}))'],
                            [4, '=IF(AND(ISNUMBER(Total improved), {self} > Total improved), Total improved, IF(AND(ISNUMBER(Total improved), NOT(ISNUMBER({self}))), Piped onto premisesLATER, {self}))'],
                            [5, '=IF(AND(ISNUMBER(Piped onto premisesURBAN), ISNUMBER(Piped onto premisesRURAL)), (Piped onto premisesURBAN * POPULATION_URBAN + Piped onto premisesRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL)'],
                        ],
                        'Tunisia' => [
                            [3, '=IF(ISNUMBER({self}), {self}, Piped onto premisesEARLIER)'],
                            [5, '=IF(AND(ISNUMBER(Piped onto premisesURBAN), ISNUMBER(Piped onto premisesRURAL)), (Piped onto premisesURBAN * POPULATION_URBAN + Piped onto premisesRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL)'],
                        ],
                        'Saudi Arabia' => [
                            [3, '=Piped onto premisesTOTAL'],
                            [4, '=Piped onto premisesTOTAL'],
                        ],
                    ],
                ],
                "Surface water" => [
                    'row' => 87,
                    'thematic' => 4,
                    'children' => [60],
                    'excludes' => 93,
                    'sorting' => 102,
                    'color' => "#e46c0a",
                    'isImproved' => false,
                    'regressionRules' => [
                        'default' => [
                            [3, '=IF(ISNUMBER(Total improved), IF(Total improved >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'],
                            [4, '=IF(ISNUMBER(Total improved), IF(Total improved >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'],
                            [5, '=IF(Total improved = 100%, 0%, IF(AND(ISNUMBER(Surface waterURBAN), ISNUMBER(Surface waterRURAL)), (Surface waterURBAN * POPULATION_URBAN + Surface waterRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL))'],
                        ],
                        'onlyTotal' => [
                            [3, '=Surface waterTOTAL'],
                            [4, '=Surface waterTOTAL'],
                            [5, '=IF(Total improved = 100%, 0%, {self})'],
                        ],
                        'onlyRural' => [
                            [4, '=IF(ISNUMBER(Total improved), IF(Total improved >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'],
                            [5, '=Surface waterRURAL'],
                        ],
                        'onlyUrban' => [
                            [3, '=IF(ISNUMBER(Total improved), IF(Total improved >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'],
                            [5, '=Surface waterURBAN'],
                        ],
                        'Belarus' => [
                            [3, '=IF(ISNUMBER(Total improved), IF(Total improved >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'],
                            [4, '=IF(ISNUMBER(Total improved), IF(Total improved >= 99%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'],
                            [5, '=IF(Total improved = 100%, 0%, IF(AND(ISNUMBER(Surface waterURBAN), ISNUMBER(Surface waterRURAL)), (Surface waterURBAN * POPULATION_URBAN + Surface waterRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL))'],
                        ],
                        'TFYR Macedonia' => [
                            [3, '=IF(ISNUMBER(Total improved), IF(Total improved >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'],
                            [4, '=Surface waterURBAN'],
                            [5, '=IF(Total improved = 100%, 0%, IF(AND(ISNUMBER(Surface waterURBAN), ISNUMBER(Surface waterRURAL)), (Surface waterURBAN * POPULATION_URBAN + Surface waterRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL))'],
                        ],
                        'United States of America' => [
                            [3, '=IF(ISNUMBER(Total improved), IF(Total improved >= 99.3%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'],
                            [4, '=IF(ISNUMBER(Total improved), IF(Total improved >= 93.6%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Surface waterLATER)), Surface waterLATER, IF(AND(ISNUMBER({self}), Total improved + {self} >= 100%), 100% - Total improved, {self}))), NULL)'],
                            [5, '=IF(Total improved = 100%, 0%, IF(AND(ISNUMBER(Surface waterURBAN), ISNUMBER(Surface waterRURAL)), (Surface waterURBAN * POPULATION_URBAN + Surface waterRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL))'],
                        ],
                        'Saudi Arabia' => [
                            [3, '=Surface waterTOTAL'],
                            [4, '=Surface waterTOTAL'],
                            [5, '=IF(Total improved = 100%, 0%, {self})'],
                        ],
                    ],
                ],
                "Other Improved" => [
                    'row' => null,
                    'thematic' => 4,
                    'children' => [9, 10, 12, 55, 58, 68],
                    'excludes' => null,
                    'sorting' => 104,
                    'color' => "#99ccff",
                    'isImproved' => false,
                    'rule' => '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)',
                    'regressionRules' => [
                        'default' => [
                            [3, '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)'],
                            [4, '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)'],
                            [5, '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)'],
                        ],
                        'onlyTotal' => [
                            [3, '=Other ImprovedTOTAL'],
                            [4, '=Other ImprovedTOTAL'],
                            [5, '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)'],
                        ],
                        'onlyRural' => [
                            [4, '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)'],
                            [5, '=Other ImprovedRURAL'],
                        ],
                        'onlyUrban' => [
                            [3, '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)'],
                            [5, '=Other ImprovedURBAN'],
                        ],
                        'Saudi Arabia' => [
                            [3, '=Other ImprovedTOTAL'],
                            [4, '=Other ImprovedTOTAL'],
                            [5, '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Piped onto premises)), Total improved - Piped onto premises, NULL)'],
                        ],
                    ],
                ],
                "Other Unimproved" => [
                    'row' => null,
                    'thematic' => 4,
                    'children' => [56, 59, 71],
                    'excludes' => null,
                    'sorting' => 103,
                    'color' => "#fdcb0a",
                    'isImproved' => false,
                    'rule' => '=IF(AND(ISNUMBER(Total improved), ISNUMBER(Surface water)), 100% - Total improved - Surface water, NULL)',
                    'regressionRules' => [
                        'default' => [
                            [3, '=IF(ISNUMBER(Total improved), IF(Total improved = 100%, 0%, 100% - Total improved - Surface water), NULL)'],
                            [4, '=IF(ISNUMBER(Total improved), IF(Total improved = 100%, 0%, 100% - Total improved - Surface water), NULL)'],
                            [5, '=IF(ISNUMBER(Total improved), IF(Total improved = 100%, 0%, 100% - Total improved - Surface water), NULL)'],
                        ],
                        'onlyTotal' => [
                            [3, '=Other UnimprovedTOTAL'],
                            [4, '=Other UnimprovedTOTAL'],
                            [5, '=IF(ISNUMBER(Total improved), IF(Total improved = 100%, 0%, 100% - Total improved - Surface water), NULL)'],
                        ],
                        'onlyRural' => [
                            [4, '=IF(ISNUMBER(Total improved), IF(Total improved = 100%, 0%, 100% - Total improved - Surface water), NULL)'],
                            [5, '=Other UnimprovedRURAL'],
                        ],
                        'onlyUrban' => [
                            [3, '=IF(ISNUMBER(Total improved), IF(Total improved = 100%, 0%, 100% - Total improved - Surface water), NULL)'],
                            [5, '=Other UnimprovedURBAN'],
                        ],
                        'Saudi Arabia' => [
                            [3, '=Other UnimprovedTOTAL'],
                            [4, '=Other UnimprovedTOTAL'],
                            [5, '=IF(ISNUMBER(Total improved), IF(Total improved = 100%, 0%, 100% - Total improved - Surface water), NULL)'],
                        ],
                    ],
                ],
            ],
            'extras' => [
                104 => '[Water] Number of people covered by the questionnaire',
                105 => '[Water] Number of households covered by the questionnaire',
                106 => '[Water] Total population',
            ],
        ],
        'Tables_S' => [
            'definitions' => [
                3 => ["JMP", null, 0, null],
                4 => ["Sanitation", 3, 0, null],
                5 => ["Flush and pour flush", 4, 2, [11, 30], 999],
                6 => ["to piped sewer system", 5, 1, [12, 31], null, "#ffff87"],
                7 => ["to septic tank", 5, 1, [13, 32], null, "#c3ffc0"],
                8 => ["to pit", 5, 1, [14, 33], null, "#c3ffc0"],
                9 => ["to unknown place/ not sure/DK", 5, 1, [15, 34], null, "#c3ffc0"],
                10 => ["to elsewhere", 5, 1, [16, 35]],
                11 => ["Flush/toilets", 4, 0, null],
                12 => ["to piped sewer system", 11, 1, [18, 24], null, "#ffff87"],
                13 => ["to septic tank", 11, 1, [19, 25], null, "#c3ffc0"],
                14 => ["to pit", 11, 1, [20, 26], null, "#c3ffc0"],
                15 => ["to unknown place/ not sure/DK", 11, 1, [21, 27], null, "#c3ffc0"],
                16 => ["to elsewhere", 11, 1, [22, 28]],
                17 => ["Private flush/toilet", 11, 0, null],
                18 => ["to piped sewer system", 17, 0, null, null, "#ffff87"],
                19 => ["to septic tank", 17, 0, null, null, "#c3ffc0"],
                20 => ["to pit", 17, 0, null, null, "#c3ffc0"],
                21 => ["to unknown place/ not sure/DK", 17, 0, null, null, "#c3ffc0"],
                22 => ["to elsewhere", 17, 0, null],
                23 => ["Public/shared flush/toilet", 11, 0, null],
                24 => ["to piped sewer system", 23, 0, null, null, "#c3ffc0"],
                25 => ["to septic tank", 23, 0, null, null, "#c3ffc0"],
                26 => ["to pit", 23, 0, null, null, "#c3ffc0"],
                27 => ["to unknown place/ not sure/DK", 23, 0, null, null, "#c3ffc0"],
                28 => ["to elsewhere", 23, 0, null],
                29 => ["Latrines", 4, 0, null],
                30 => ["Pour flush latrines", 29, 0, null],
                31 => ["to piped sewer system", 30, 1, [37, 43], null, "#ffff87"],
                32 => ["to septic tank", 30, 1, [38, 44], null, "#c3ffc0"],
                33 => ["to pit", 30, 1, [39, 45], null, "#c3ffc0"],
                34 => ["to unknown place/ not sure/DK", 30, 1, [40, 46], null, "#c3ffc0"],
                35 => ["to elsewhere", 30, 1, [41, 47]],
                36 => ["Private pour flush latrine", 30, 0, null],
                37 => ["to piped sewer system", 36, 0, null, null, "#ffff87"],
                38 => ["to septic tank", 36, 0, null, null, "#c3ffc0"],
                39 => ["to pit", 36, 0, null, null, "#c3ffc0"],
                40 => ["to unknown place/ not sure/DK", 36, 0, null, null, "#c3ffc0"],
                41 => ["to elsewhere", 36, 0, null],
                42 => ["Public/shared pour flush latrine", 30, 0, null],
                43 => ["to piped sewer system", 42, 0, null, null, "#ffff87"],
                44 => ["to septic tank", 42, 0, null, null, "#c3ffc0"],
                45 => ["to pit", 42, 0, null, null, "#c3ffc0"],
                46 => ["to unknown place/ not sure/DK", 42, 0, null, null, "#c3ffc0"],
                47 => ["to elsewhere", 42, 0, null],
                48 => ["Dry latrines", 29, 0, null],
                49 => ["Improved latrines", 48, 0, null, null, "#c3ffc0"],
                50 => ["Ventilated Improved Pit latrine", 49, 1, [58, 66], null, "#c3ffc0"],
                51 => ["Pit latrine with slab/covered latrine", 49, 1, [59, 67], null, "#c3ffc0"],
                52 => ["Traditional latrine", 48, 1, [60, 68]],
                53 => ["Pit latrine without slab/open pit", 48, 1, [61, 69]],
                54 => ["Hanging toilet/hanging latrine", 48, 1, [62, 70]],
                55 => ["Bucket latrine", 48, 1, [63, 71]],
                56 => ["Other", 48, 1, [64, 72]],
                57 => ["Private Latrines", 48, 0, null],
                58 => ["Ventilated Improved Pit latrine", 57, 0, null, null, "#c3ffc0"],
                59 => ["Pit latrine with slab/covered latrine", 57, 0, null, null, "#c3ffc0"],
                60 => ["Traditional latrine", 57, 0, null],
                61 => ["Pit latrine without slab/open pit", 57, 0, null],
                62 => ["Hanging toilet/hanging latrine", 57, 0, null],
                63 => ["Bucket latrine", 57, 0, null],
                64 => ["Other", 57, 0, null],
                65 => ["Public/shared Latrines", 48, 0, null],
                66 => ["Ventilated Improved Pit latrine", 65, 0, null, null, "#c3ffc0"],
                67 => ["Pit latrine with slab/covered latrine", 65, 0, null, null, "#c3ffc0"],
                68 => ["Traditional latrine", 65, 0, null],
                69 => ["Pit latrine without slab/open pit", 65, 0, null],
                70 => ["Hanging toilet/hanging latrine", 65, 0, null],
                71 => ["Bucket latrine", 65, 0, null],
                72 => ["Other", 65, 0, null],
                73 => ["Composting toilets", 4, 0, null, null, "#c3ffc0"],
                74 => ["Composting toilet (private)", 73, 0, null, null, "#c3ffc0"],
                75 => ["Composting toilet (shared)", 73, 0, null, null, "#c3ffc0"],
                76 => ["Other improved", 4, 0, null, null, "#c3ffc0"],
                77 => ["Other 1", 76, 0, null, null, "#c3ffc0"],
                78 => ["Other 2", 76, 0, null, null, "#c3ffc0"],
                79 => ["No facility, bush, field ", 4, 0, null, null, "#fb8654"],
                80 => ["Other unimproved", 4, 0, null],
                81 => ["Other 1", 80, 0, null],
                82 => ["Other 2", 80, 0, null],
                83 => ["DK/missing information", 4, 0, null],
            ],
            'replacements' => [
                // For sanitation, we need to modify the filter tree to introduce a new branch
                999 => ["Other flush and pour flush", 5, 0, null],
                6 => ["to piped sewer system", 999, 0, null],
                7 => ["to septic tank", 999, 0, null],
                8 => ["to pit", 999, 0, null],
                9 => ["to unknown place/ not sure/DK", 999, 0, null],
                10 => ["to elsewhere", 999, 0, null],
            ],
            'cellReplacements' => [
            // No cell replacements to make for sanitation
            ],
            'questionnaireUsages' => [
                'Thematic' => 4,
                'Groups' => [
                    'Calculation' => [85, 86, 87],
                    'Estimate' => [88, 89, 90, 91, 92, 93],
                    'Ratio' => [100, 101, 102, 103, 104, 105, 106, 107],
                ],
            ],
            'ratios' => [
                'min' => 100,
                'max' => 107,
            ],
            'filterSet' => [
                'improvedName' => 'Sanitation: use of improved facilities (JMP data)',
                'improvedUnimprovedName' => 'Sanitation: use of improved and unimproved facilities (JMP data)',
            ],
            'highFilters' => [
                "Improved + shared" => [
                    'row' => 94,
                    'thematic' => 4,
                    'children' => [-6, -7, -8, -9, 49, 73, 76],
                    'excludes' => 96,
                    'sorting' => 200,
                    'color' => '#339966',
                    'isImproved' => true,
                    'regressionRules' => [
                        'default' => [
                            [5, '=IF(ISNUMBER(Shared), Shared + Improved, IF(AND(ISNUMBER(Improved + sharedURBAN), ISNUMBER(Improved + sharedRURAL)), (Improved + sharedURBAN * POPULATION_URBAN + Improved + sharedRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL))'],
                        ],
                        'onlyTotal' => [
                            [3, '=Improved + sharedTOTAL'],
                            [4, '=Improved + sharedTOTAL'],
                        ],
                        'onlyRural' => [
                            [5, '=Improved + sharedRURAL'],
                        ],
                        'onlyUrban' => [
                            [5, '=Improved + sharedURBAN'],
                        ],
                        'Saudi Arabia' => [
                            [3, '=Improved + sharedTOTAL'],
                            [4, '=Improved + sharedTOTAL'],
                        ],
                    ],
                ],
                "Sewerage connections" => [
                    'row' => 95,
                    'thematic' => 4,
                    'children' => [6],
                    'excludes' => 97,
                    'sorting' => 201,
                    'color' => '#FFFF99',
                    'isImproved' => false,
                    'regressionRules' => [
                        'default' => [
                        ],
                        'onlyTotal' => [
                            [3, '=Sewerage connectionsTOTAL'],
                            [4, '=Sewerage connectionsTOTAL'],
                        ],
                        'onlyRural' => [
                            [5, '=Sewerage connectionsRURAL'],
                        ],
                        'onlyUrban' => [
                            [5, '=Sewerage connectionsURBAN'],
                        ],
                        'Saudi Arabia' => [
                            [3, '=Sewerage connectionsTOTAL'],
                            [4, '=Sewerage connectionsTOTAL'],
                        ],
                    ],
                ],
                "Improved" => [
                    'row' => null,
                    'thematic' => 4,
                    'children' => [], // based on ratio
                    'excludes' => null,
                    'sorting' => 205,
                    'color' => "#168000",
                    'isImproved' => true,
                    'regressionRules' => [
                        'default' => [
                            // Additionnal rules for developed countries
                            [3, "=IF(AND(Improved + shared > 99.5%, COUNT(ALL_RATIOS) = 0), Improved + shared, {self})", true],
                            [4, "=IF(AND(Improved + shared > 99.5%, COUNT(ALL_RATIOS) = 0), Improved + shared, {self})", true],
                            // Normal rules
                            [3, "=IF(AND(ISNUMBER(Improved + shared), COUNT(ALL_RATIOS) > 0), Improved + shared * (100% - AVERAGE(ALL_RATIOS)), NULL)"],
                            [4, "=IF(AND(ISNUMBER(Improved + shared), COUNT(ALL_RATIOS) > 0), Improved + shared * (100% - AVERAGE(ALL_RATIOS)), NULL)"],
                            [5, "=IF(AND(ISNUMBER(ImprovedURBAN), ISNUMBER(ImprovedRURAL)), (ImprovedURBAN * POPULATION_URBAN + ImprovedRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL)"],
                        ],
                        'onlyTotal' => [
                            [3, '=ImprovedTOTAL'],
                            [4, '=ImprovedTOTAL'],
                            [5, "=IF(AND(ISNUMBER(Improved + shared), COUNT(ALL_RATIOS) > 0), Improved + shared * (100% - AVERAGE(ALL_RATIOS)), NULL)"],
                        ],
                        'onlyRural' => [
                            // Additionnal rules for developed countries
                            [4, "=IF(AND(Improved + shared > 99.5%, COUNT(ALL_RATIOS) = 0), Improved + shared, {self})", true],
                            // Normal rules
                            [4, "=IF(AND(ISNUMBER(Improved + shared), COUNT(ALL_RATIOS) > 0), Improved + shared * (100% - AVERAGE(ALL_RATIOS)), NULL)"],
                            [5, '=ImprovedRURAL'],
                        ],
                        'onlyUrban' => [
                            // Additionnal rules for developed countries
                            [3, "=IF(AND(Improved + shared > 99.5%, COUNT(ALL_RATIOS) = 0), Improved + shared, {self})", true],
                            // Normal rules
                            [3, "=IF(AND(ISNUMBER(Improved + shared), COUNT(ALL_RATIOS) > 0), Improved + shared * (100% - AVERAGE(ALL_RATIOS)), NULL)"],
                            [5, '=ImprovedURBAN'],
                        ],
                        'United States of America' => [
                            // Additionnal rules for developed countries
                            [3, "=IF(AND(Improved + shared > 99.5%, COUNT(ALL_RATIOS) = 0), Improved + shared, {self})", true],
                            [4, "=IF(AND(Improved + shared > 98.4%, COUNT(ALL_RATIOS) = 0), Improved + shared, {self})", true],
                            // Normal rules
                            [3, "=IF(AND(ISNUMBER(Improved + shared), COUNT(ALL_RATIOS) > 0), Improved + shared * (100% - AVERAGE(ALL_RATIOS)), NULL)"],
                            [4, "=IF(AND(ISNUMBER(Improved + shared), COUNT(ALL_RATIOS) > 0), Improved + shared * (100% - AVERAGE(ALL_RATIOS)), NULL)"],
                            [5, "=IF(AND(ISNUMBER(ImprovedURBAN), ISNUMBER(ImprovedRURAL)), (ImprovedURBAN * POPULATION_URBAN + ImprovedRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL)"],
                        ],
                        'Saudi Arabia' => [
                            [3, '=ImprovedTOTAL'],
                            [4, '=ImprovedTOTAL'],
                            [5, "=IF(AND(ISNUMBER(Improved + shared), COUNT(ALL_RATIOS) > 0), Improved + shared * (100% - AVERAGE(ALL_RATIOS)), NULL)"],
                        ],
                    ],
                ],
                "Shared" => [
                    'row' => null,
                    'thematic' => 4,
                    'children' => [], // based on ratio
                    'excludes' => null,
                    'sorting' => 204,
                    'color' => "#99cc03",
                    'isImproved' => false,
                    'regressionRules' => [
                        'default' => [
                            [3, '=IF(AND(ISNUMBER(Improved + shared), ISNUMBER(Improved)), Improved + shared - Improved, NULL)'],
                            [4, '=IF(AND(ISNUMBER(Improved + shared), ISNUMBER(Improved)), Improved + shared - Improved, NULL)'],
                            [5, "=IF(AND(ISNUMBER(SharedURBAN), ISNUMBER(SharedRURAL)), (SharedURBAN * POPULATION_URBAN + SharedRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL)"],
                        ],
                        'onlyTotal' => [
                            [3, '=SharedTOTAL'],
                            [4, '=SharedTOTAL'],
                            [5, "=IF(AND(ISNUMBER(Improved + shared), ISNUMBER(Improved)), Improved + shared - Improved, NULL)"],
                        ],
                        'onlyRural' => [
                            [4, '=IF(AND(ISNUMBER(Improved + shared), ISNUMBER(Improved)), Improved + shared - Improved, NULL)'],
                            [5, '=SharedRURAL'],
                        ],
                        'onlyUrban' => [
                            [3, '=IF(AND(ISNUMBER(Improved + shared), ISNUMBER(Improved)), Improved + shared - Improved, NULL)'],
                            [5, '=SharedURBAN'],
                        ],
                        'Saudi Arabia' => [
                            [3, '=SharedTOTAL'],
                            [4, '=SharedTOTAL'],
                            [5, "=IF(AND(ISNUMBER(Improved + shared), ISNUMBER(Improved)), Improved + shared - Improved, NULL)"],
                        ],
                    ],
                ],
                "Other unimproved" => [
                    'row' => null,
                    'thematic' => 4,
                    'children' => [80],
                    'excludes' => null,
                    'sorting' => 203,
                    'color' => "#fdcb00",
                    'isImproved' => false,
                    'rule' => '=IF(AND(ISNUMBER(Improved + shared), ISNUMBER(Open defecation)), 100% - Improved + shared - Open defecation, NULL)',
                    'regressionRules' => [
                        'default' => [
                            [3, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared = 100%, 0%, 100% - Improved + shared - Open defecation), NULL)'],
                            [4, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared = 100%, 0%, 100% - Improved + shared - Open defecation), NULL)'],
                            [5, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared + Open defecation >= 100%, 0%, IF(Improved + shared = 100%, 0%, 100% - Improved + shared - Open defecation)), NULL)'],
                        ],
                        'onlyTotal' => [
                            [3, '=Other unimprovedTOTAL'],
                            [4, '=Other unimprovedTOTAL'],
                            [5, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared + Open defecation >= 100%, 0%, IF(Improved + shared = 100%, 0%, 100% - Improved + shared - Open defecation)), NULL)'],
                        ],
                        'onlyRural' => [
                            [4, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared = 100%, 0%, 100% - Improved + shared - Open defecation), NULL)'],
                            [5, '=Other unimprovedRURAL'],
                        ],
                        'onlyUrban' => [
                            [3, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared = 100%, 0%, 100% - Improved + shared - Open defecation), NULL)'],
                            [5, '=Other unimprovedURBAN'],
                        ],
                        'Saudi Arabia' => [
                            [3, '=Other unimprovedTOTAL'],
                            [4, '=Other unimprovedTOTAL'],
                            [5, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared + Open defecation >= 100%, 0%, IF(Improved + shared = 100%, 0%, 100% - Improved + shared - Open defecation)), NULL)'],
                        ],
                    ],
                ],
                "Open defecation" => [
                    'row' => null,
                    'thematic' => 4,
                    'children' => [79],
                    'excludes' => 98,
                    'sorting' => 202,
                    'color' => "#e36c0a",
                    'isImproved' => false,
                    'regressionRules' => [
                        'default' => [
                            [3, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Open defecationLATER)), Open defecationLATER, IF(AND(ISNUMBER({self}), Improved + shared + {self} >= 100%), 100% - Improved + shared, {self}))), NULL)'],
                            [4, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Open defecationLATER)), Open defecationLATER, IF(AND(ISNUMBER({self}), Improved + shared + {self} >= 100%), 100% - Improved + shared, {self}))), NULL)'],
                            [5, '=IF(Improved + shared = 100%, 0%, IF(AND(ISNUMBER(Open defecationURBAN), ISNUMBER(Open defecationRURAL)), (Open defecationURBAN * POPULATION_URBAN + Open defecationRURAL * POPULATION_RURAL) / POPULATION_TOTAL, NULL))'],
                        ],
                        'onlyTotal' => [
                            [3, '=Open defecationTOTAL'],
                            [4, '=Open defecationTOTAL'],
                            [5, '=IF(Improved + shared = 100%, 0%, {self})'],
                        ],
                        'onlyRural' => [
                            [4, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Open defecationLATER)), Open defecationLATER, IF(AND(ISNUMBER({self}), Improved + shared + {self} >= 100%), 100% - Improved + shared, {self}))), NULL)'],
                            [5, '=Open defecationRURAL'],
                        ],
                        'onlyUrban' => [
                            [3, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared >= 99.5%, 0%, IF(AND(NOT(ISNUMBER({self})), ISNUMBER(Open defecationLATER)), Open defecationLATER, IF(AND(ISNUMBER({self}), Improved + shared + {self} >= 100%), 100% - Improved + shared, {self}))), NULL)'],
                            [5, '=Open defecationURBAN'],
                        ],
                        'Saudi Arabia' => [
                            [3, '=Open defecationTOTAL'],
                            [4, '=Open defecationTOTAL'],
                            [5, '=IF(ISNUMBER(Improved + shared), IF(Improved + shared >= 100%, 0%, IF(OR(AND(ISNUMBER({self}), Improved + shared + {self} >= 100%), NOT(ISNUMBER({self}))), 100% - Improved + shared, {self})), NULL)'],
                        ],
                    ],
                ],
            ],
            'extras' => [
                108 => '[Sanitation] Number of people covered by the questionnaire',
                109 => '[Sanitation] Number of households covered by the questionnaire',
                110 => '[Sanitation] Total population',
            ],
        ],
    ];

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

        $filterRepository = $this->getEntityManager()->getRepository(\Application\Model\Filter::class);
        $filter = $filterRepository->getOneByNames($name, $parentName);
        if (!$filter) {
            $filter = new Filter($name);
            $filter->setBgColor(@$definition[5]);

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
