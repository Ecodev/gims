<?php

namespace Application\Service\Importer;

use Application\Model\Filter;

abstract class AbstractImporter
{
    use \Zend\ServiceManager\ServiceLocatorAwareTrait;
    use \Application\Traits\EntityManagerAware;

    protected $cacheQuestions = array();
    protected $cacheFilters = array();
    protected $cacheHighFilters = array();

    /**
     * sheet name =>
     *      defintions =>
     *          row => name, parent row, computing type, summands rows
     * @var array
     */
    protected $definitions = array(

          // Glass filters
//        'Tables_U' => array(
//            'definitions' => array(
//                4 => array("Undefined", null, 0, null),
//                5 => array("Central government", 4, 0, null),
//                6 => array("Undefined Bilateral, multilateral donors", 4, 0, null),
//                7 => array("Undefined State, provincial, local", 4, 0, null),
//                8 => array("Undefined Tariffs and charges", 4, 0, null),
//                9 => array("Undefined Households’ out-of-pocket", 4, 0, null),
//                10 => array("Undefined NGOs, Lendors, Other", 4, 0, null),
//                11 => array("Undefined total Central government", 4, 0, null),
//                12 => array("Undefined total Bilateral, multilateral donors", 4, 0, null),
//                13 => array("Undefined total State, provincial, local", 4, 0, null),
//                14 => array("Undefined total Tariffs and charges", 4, 0, null),
//                15 => array("Undefined total Households’ out-of-pocket", 4, 0, null),
//                16 => array("Undefined total NGOs, Lendors, Other", 4, 0, null),
//                17 => array("Identify public health priorities", 4, 0, null),
//                18 => array("Response to WASH related disease outbreak", 4, 0, null),
//                19 => array("Universal access for disadvantaged groups", 4, 0, null),
//                20 => array("Overall coordination between WASH actors:", 4, 0, null),
//
//            ),
//            'replacements' => array(
//                // No replacements to make for water
//            ),
//            'filterSet' => array(
//                'improvedName' => 'Funding Sources',
//            ),
//        ),
//        'Tables_H' => array(
//            'definitions' => array(
//                4 => array("Hygiene", null, 0, null),
//                5 => array("Hygiene Central government", 4, 0, null),
//                6 => array("Hygiene Bilateral, multilateral donors", 4, 0, null),
//                7 => array("Hygiene State, provincial, local", 4, 0, null),
//                8 => array("Hygiene Tariffs and charges", 4, 0, null),
//                9 => array("Hygiene Households’ out-of-pocket", 4, 0, null),
//                10 => array("Hygiene NGOs, Lendors, Other", 4, 0, null),
//                11 => array("Hygiene WASH HR strategy exists", 4, 0, null),
//                12 => array("Hygiene Current difficulties in HR for WASH", 4, 0, null),
//                13 => array("Hygiene Financial resources available for staff (salaries and benefits, including pensions etc.) ", 4, 0, null),
//                14 => array("Hygiene Insufficient education/training organisations or courses to meet demand by potential students", 4, 0, null),
//                15 => array("Hygiene Lack of skilled graduates from training & education institutes", 4, 0, null),
//                16 => array("Hygiene Preference by skilled graduates to work in other (non-WASH) sectors (e.g. Mining, Transport, Construction) within the country", 4, 0, null),
//                17 => array("Hygiene Emigration (temporary or permanent) of skilled workers to work abroad", 4, 0, null),
//                18 => array("Hygiene Skilled workers do not want to live and work in rural areas of the country", 4, 0, null),
//                19 => array("Hygiene Recruitment practices", 4, 0, null),
//                20 => array("Hygiene Other (please specify)", 4, 0, null),
//                21 => array("Hygiene promotion", 4, 0, null),
//                22 => array("Hygiene promotion in schools", 4, 0, null),
//                23 => array("Hygiene promotion in health facilities", 4, 0, null),
//                24 => array("Hygiene Govern/Regulate", 4, 0, null),
//                25 => array("Hygiene Provide Service", 4, 0, null),
//                26 => array("Hygiene Monitor/Survie", 4, 0, null),
//                27 => array("Hygiene Ministry of health", 4, 0, null),
//                28 => array("Hygiene National Water and sanitation authority (urban)", 4, 0, null),
//                29 => array("Hygiene Ministry of Municipalities (rural)", 4, 0, null),
//                30 => array("Hygiene Ministry of Education", 4, 0, null),
//                31 => array("Hygiene Poor populations", 4, 0, null),
//                32 => array("Hygiene Populations living in slums or informal settlements", 4, 0, null),
//                33 => array("Hygiene Remote or hard to reach areas	", 4, 0, null),
//                34 => array("Hygiene Indigenous population", 4, 0, null),
//                35 => array("Hygiene Internally displaced", 4, 0, null),
//                36 => array("Hygiene Ethnic minorities", 4, 0, null),
//                37 => array("Hygiene People with disabilities", 4, 0, null),
//                38 => array("Hygiene Other disadvantaged groups", 4, 0, null),
//                39 => array("To ensure drinking water quality meet national standards", 4, 0, null),
//                40 => array("Hygiene Ministry of Education", 4, 0, null),
//                41 => array("To address resilience to climate change", 4, 0, null),
//            ),
//            'replacements' => array(
//                // No replacements to make for water
//            ),
//        ),
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

                // Glass filters
//                77  => array("Water Central government", 4, 0, null),
//                78  => array("Water Bilateral, multilateral donors", 4, 0, null),
//                79  => array("Water State, provincial, local", 4, 0, null),
//                80  => array("Water Tariffs and charges", 4, 0, null),
//                81  => array("Water Households’ out-of-pocket", 4, 0, null),
//                82  => array("Water NGOs, Lendors, Other", 4, 0, null),
//                83  => array("Water Expenditure", 4, 0, null),
//                84  => array("Water Service quality", 4, 0, null),
//                85  => array("Water Equitable Service", 4, 0, null),
//                86  => array("Water Cost Effectiveness", 4, 0, null),
//                87  => array("Water Functionality of systems", 4, 0, null),
//                88  => array("Water Affordability", 4, 0, null),
//                89  => array("Water Wastewater/septage reuse", 4, 0, null),
//                90  => array("Water Institutional effectiveness", 4, 0, null),
//                91  => array("Water Cost-Recovery", 4, 0, null),
//                92  => array("Water Policy and strategy", 4, 0, null),
//                93  => array("Water Resource allocation", 4, 0, null),
//                94  => array("National standards", 4, 0, null),
//                95  => array("Water WASH HR strategy exists", 4, 0, null),
//                96  => array("Water Current difficulties in HR for WASH", 4, 0, null),
//                97  => array("Water Financial resources available for staff (salaries and benefits, including pensions etc.) ", 4, 0, null),
//                98  => array("Water Insufficient education/training organisations or courses to meet demand by potential students", 4, 0, null),
//                99  => array("Water Lack of skilled graduates from training & education institutes", 4, 0, null),
//                100 => array("Water Preference by skilled graduates to work in other (non-WASH) sectors (e.g. Mining, Transport, Construction) within the country", 4, 0, null),
//                101 => array("Water Emigration (temporary or permanent) of skilled workers to work abroad", 4, 0, null),
//                102 => array("Water Skilled workers do not want to live and work in rural areas of the country", 4, 0, null),
//                103 => array("Water Recruitment practices", 4, 0, null),
//                104 => array("Water Other (please specify)", 4, 0, null),
//                105 => array("Drinking-water in schools", 4, 0, null),
//                106 => array("Drinking-water in health facilities", 4, 0, null),
//                106 => array("Water Formal", 4, 0, null),
//                106 => array("Water Community-based", 4, 0, null),
//                106 => array("Water Informal", 4, 0, null),
//                107 => array("Water Govern/Regulate", 4, 0, null),
//                108 => array("Water Provide Service", 4, 0, null),
//                109 => array("Water Monitor/Survie", 4, 0, null),
//                110 => array("Water Ministry of health", 4, 0, null),
//                111 => array("Water National Water and sanitation authority (urban)", 4, 0, null),
//                112 => array("Water Ministry of Municipalities (rural)", 4, 0, null),
//                113 => array("Water Ministry of Education", 4, 0, null),
//                114 => array("Water Poor populations", 4, 0, null),
//                115 => array("Water Populations living in slums or informal settlements", 4, 0, null),
//                116 => array("Water Remote or hard to reach areas	", 4, 0, null),
//                117 => array("Water Indigenous population", 4, 0, null),
//                118 => array("Water Internally displaced", 4, 0, null),
//                119 => array("Water Ethnic minorities", 4, 0, null),
//                120 => array("Water People with disabilities", 4, 0, null),
//                121 => array("Water Other disadvantaged groups", 4, 0, null),
//                122 => array("Water Ministry of Education", 4, 0, null),
//                123 => array("To keep rural water supplies functioning over the long-term (e.g. supply of parts, human resources for operation and maintenance, etc.)", 4, 0, null),
//                124 => array("To improve the reliability and continuity of urban water supplies", 4, 0, null),
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

                // Glass filters
//                84 => array("Sanitation Central government", 4, 0, null),
//                85 => array("Sanitation Bilateral, multilateral donors", 4, 0, null),
//                86 => array("Sanitation State, provincial, local", 4, 0, null),
//                87 => array("Sanitation Tariffs and charges", 4, 0, null),
//                88 => array("Sanitation Households’ out-of-pocket", 4, 0, null),
//                89 => array("Sanitation NGOs, Lendors, Other", 4, 0, null),
//                90 => array("Sanitation Expenditure", 4, 0, null),
//                91 => array("Sanitation Service quality", 4, 0, null),
//                92 => array("Sanitation Equitable Service", 4, 0, null),
//                93 => array("Sanitation Cost Effectiveness", 4, 0, null),
//                94 => array("Sanitation Functionality of systems", 4, 0, null),
//                95 => array("Sanitation Affordability", 4, 0, null),
//                96 => array("Sanitation Wastewater/septage reuse", 4, 0, null),
//                97 => array("Sanitation Institutional effectiveness", 4, 0, null),
//                98 => array("Sanitation Cost-Recovery", 4, 0, null),
//                99 => array("Sanitation Policy and strategy", 4, 0, null),
//                100 => array("Sanitation Resource allocation", 4, 0, null),
//                101 => array("Sanitation WASH HR strategy exists", 4, 0, null),
//                102 => array("Sanitation Current difficulties in HR for WASH", 4, 0, null),
//                103 => array("Sanitation Financial resources available for staff (salaries and benefits, including pensions etc.) ", 4, 0, null),
//                104 => array("Sanitation Insufficient education/training organisations or courses to meet demand by potential students", 4, 0, null),
//                105 => array("Sanitation Lack of skilled graduates from training & education institutes", 4, 0, null),
//                106 => array("Sanitation Preference by skilled graduates to work in other (non-WASH) sectors (e.g. Mining, Transport, Construction) within the country", 4, 0, null),
//                107 => array("Sanitation Emigration (temporary or permanent) of skilled workers to work abroad", 4, 0, null),
//                108 => array("Sanitation Skilled workers do not want to live and work in rural areas of the country", 4, 0, null),
//                109 => array("Sanitation Recruitment practices", 4, 0, null),
//                110 => array("Sanitation Other (please specify)", 4, 0, null),
//                111 => array("Sanitation in schools", 4, 0, null),
//                112 => array("Sanitation in health facilities", 4, 0, null),
//                113 => array("Sanitation Formal", 4, 0, null),
//                114 => array("Sanitation Community-based", 4, 0, null),
//                115 => array("Sanitation Informal", 4, 0, null),
//                116 => array("Sanitation Govern/Regulate", 4, 0, null),
//                117 => array("Sanitation Provide Service", 4, 0, null),
//                118 => array("Sanitation Monitor/Survie", 4, 0, null),
//                119 => array("Sanitation Ministry of health", 4, 0, null),
//                120 => array("Sanitation National Water and sanitation authority (urban)", 4, 0, null),
//                121 => array("Sanitation Ministry of Municipalities (rural)", 4, 0, null),
//                122 => array("Sanitation Ministry of Education", 4, 0, null),
//                123 => array("Sanitation Poor populations", 4, 0, null),
//                124 => array("Sanitation Populations living in slums or informal settlements", 4, 0, null),
//                125 => array("Sanitation Remote or hard to reach areas	", 4, 0, null),
//                126 => array("Sanitation Indigenous population", 4, 0, null),
//                127 => array("Sanitation Internally displaced", 4, 0, null),
//                128 => array("Sanitation Ethnic minorities", 4, 0, null),
//                129 => array("Sanitation People with disabilities", 4, 0, null),
//                130 => array("Sanitation Other disadvantaged groups", 4, 0, null),
//                131 => array("Sanitation Ministry of Education", 4, 0, null),
//                132 => array("To rehabilitate broken or disused public latrines  (e.g. in schools)", 4, 0, null),
//                133 => array("To safely empty or replace latrines when full ", 4, 0, null),
//                134 => array("To reuse wastewater and/or septage", 4, 0, null),
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

}