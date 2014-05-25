<?php

namespace Application\Service\Importer;

use Application\Model\FilterSet;
use Application\Traits\EntityManagerAware;

class Glass extends AbstractImporter
{

    use EntityManagerAware;

    private $linkedFilterAcrossTableCount = 0;
    private $linkedFilterSetCount = 0;
    private $linkedQuestionCount = 0;
    private $cacheFiltersGlass = array(
        'Tables_W' => array(),
        'Tables_S' => array(),
        'Tables_H' => array(),
        'Tables_U' => array()
    );

    /**
     *
     * @var array [[name, [table => filterIndexes]]]
     */
    private $filterSets = [
        [
            'Level of development and implementation of policy / plan',
            [
                'Tables_W' => [10, 11, 12, 13, 20, 21, 22, 23, 30, 31, 32, 33],
                'Tables_S' => [10, 11, 12, 13, 20, 21, 22, 23, 30, 31, 32, 33],
                'Tables_H' => [10, 11, 12, 13, 20, 21, 22, 23, 30, 31, 32, 33],
                'Tables_U' => [],
            ]
        ],
        [
            'Elements of sustainability in policy and level of implementation',
            [
                'Tables_W' => [40, 41, 42],
                'Tables_S' => [40, 41, 42],
                'Tables_H' => [],
                'Tables_U' => [40],
            ]
        ],
        [
            'Level of WASH responsibilities of Ministries or National Institutions',
            [
                'Tables_W' => [60, 61, 62, 63, 70, 71, 72, 73, 80, 81, 82, 83, 90, 91, 92, 93, 100, 101, 102, 103],
                'Tables_S' => [60, 61, 62, 63, 70, 71, 72, 73, 80, 81, 82, 83, 90, 91, 92, 93, 100, 101, 102, 103],
                'Tables_H' => [60, 61, 62, 63, 70, 71, 72, 73, 80, 81, 82, 83, 90, 91, 92, 93, 100, 101, 102, 103],
                'Tables_U' => [],
            ]
        ],
        [
            'Distribution of service provision for drinking-water by institutional type',
            [
                'Tables_W' => [50, 51, 52, 53],
                'Tables_S' => [],
                'Tables_H' => [],
                'Tables_U' => [],
            ]
        ],
        [
            'Distribution of service provision for sanitation by institutional type',
            [
                'Tables_W' => [],
                'Tables_S' => [50, 51, 52, 53],
                'Tables_H' => [],
                'Tables_U' => [],
            ]
        ],
        [
            'Disadvantaged groups identified in WASH plan and groups with monitoring systems',
            [
                'Tables_W' => [110, 111, 112, 113, 114, 115, 116, 117, 118],
                'Tables_S' => [110, 111, 112, 113, 114, 115, 116, 117, 118],
                'Tables_H' => [110, 111, 112, 113, 114, 115, 116, 117, 118],
                'Tables_U' => [],
            ]
        ],
        [
            'Existence and implementation of performance indicators',
            [
                'Tables_W' => [120, 121, 122, 123, 124, 125, 127, 128],
                'Tables_S' => [120, 121, 122, 123, 124, 125, 126, 127, 128],
                'Tables_H' => [],
                'Tables_U' => [],
            ]
        ],
        [
            'Data availability',
            [
                'Tables_W' => [130, 131, 132,],
                'Tables_S' => [130, 131],
                'Tables_H' => [],
                'Tables_U' => [130, 131],
            ]
        ],
//        [
//            'Human Resources',
//            [
//                'Tables_W' => [140, 141, 142, 143, 144, 145, 146, 147, 148, 149],
//                'Tables_S' => [140, 141, 142, 143, 144, 145, 146, 147, 148, 149],
//                'Tables_H' => [140, 141, 142, 143, 144, 145, 146, 147, 148, 149],
//                'Tables_U' => [],
//            ]
//        ],
        [
            'Funding Sources',
            [
                'Tables_W' => [],
                'Tables_S' => [],
                'Tables_H' => [],
                'Tables_U' => [160, 161, 163, 164, 165, 166],
            ]
        ],
//        [
//            'Sufficiency of finance to meet MDG targets',
//            [
//                'Tables_W' => [],
//                'Tables_S' => [],
//                'Tables_H' => [],
//                'Tables_U' => [],
//            ]
//        ],
//        [
//            'Absorption of funds',
//            [
//                'Tables_W' => [],
//                'Tables_S' => [],
//                'Tables_H' => [],
//                'Tables_U' => [],
//            ]
//        ],
//        [
//            'Existence  and extent of implementation of Finance plan',
//            [
//                'Tables_W' => [],
//                'Tables_S' => [],
//                'Tables_H' => [],
//                'Tables_U' => [],
//            ]
//        ],
//        [
//            'WASH  Expenditure in Sanitation,  Drinking-water and Hygiene',
//            [
//                'Tables_W' => [],
//                'Tables_S' => [],
//                'Tables_H' => [],
//                'Tables_U' => [],
//            ]
//        ],
//        [
//            'Existence of national assessment for WASH areas',
//            [
//                'Tables_W' => [],
//                'Tables_S' => [],
//                'Tables_H' => [],
//                'Tables_U' => [],
//            ]
//        ],
    ];
    private $filters = array(
        'Tables_W' => array(
            1 => array("Water", null),
            9 => array("Level of development and implementation of policy / plan", 1), // kind of unique parent to avoid conflits between other Drinking water supply filters and this one
            10 => array("Drinking-water supply", 9),
            11 => array("Target coverage", 10),
            12 => array("Target year", 10),
            13 => array("Level of development / Implementation of plan", 10),
            20 => array("Drinking-water in schools", 9),
            21 => array("Target coverage", 20),
            22 => array("Target year", 20),
            23 => array("Level of development / Implementation of plan", 20),
            30 => array("Drinking-water in health facilities", 9),
            31 => array("Target coverage", 30),
            32 => array("Target year", 30),
            33 => array("Level of development / Implementation of plan", 30),
            40 => array("To keep rural water supplies functioning over the long-term", 1),
            41 => array("To improve the reliability and continuity of urban water supplies", 1),
            42 => array("To ensure drinking water quality meet national standards", 1),
            50 => array("Formal", 1),
            51 => array("Community-based", 1),
            52 => array("Informal", 1),
            53 => array("Self-supply", 1),
            60 => array("Ministry1", 1),
            61 => array("Govern/Regulate", 60),
            62 => array("Provide Service", 60),
            63 => array("Monitor/Survie", 60),
            70 => array("Ministry2", 1),
            71 => array("Govern/Regulate", 70),
            72 => array("Provide Service", 70),
            73 => array("Monitor/Survie", 70),
            80 => array("Ministry3", 1),
            81 => array("Govern/Regulate", 80),
            82 => array("Provide Service", 80),
            83 => array("Monitor/Survie", 80),
            90 => array("Ministry4", 1),
            91 => array("Govern/Regulate", 90),
            92 => array("Provide Service", 90),
            93 => array("Monitor/Survie", 90),
            100 => array("Ministry5", 1),
            101 => array("Govern/Regulate", 100),
            102 => array("Provide Service", 100),
            103 => array("Monitor/Survie", 100),
            110 => array("Poor populations", 1),
            111 => array("Populations living in slums or informal settlements", 1),
            112 => array("Remote or hard to reach areas	", 1),
            113 => array("Indigenous population", 1),
            114 => array("Internally displaced", 1),
            115 => array("Ethnic minorities", 1),
            116 => array("People with disabilities", 1),
            117 => array("Other disadvantaged groups name", 1),
            118 => array("Other disadvantaged groups", 1),
            120 => array("Expenditure", 1),
            121 => array("Service quality", 1),
            122 => array("Equitable Service", 1),
            123 => array("Cost Effectiveness", 1),
            124 => array("Functionality of systems", 1),
            125 => array("Affordability", 1),
            // 126  => array("Wastewater/septage reuse", 1), // not for water
            127 => array("Institutional effectiveness", 1),
            128 => array("Cost-Recovery", 1),
            130 => array("Policy and strategy", 1),
            131 => array("Resource allocation", 1),
            132 => array("National standards", 1),
            140 => array("WASH HR strategy exists", 1), //
            141 => array("Current difficulties in HR for WASH", 1),
            142 => array("Financial resources available for staff (salaries and benefits, including pensions etc.) ", 1),
            143 => array("Insufficient education/training organisations or courses to meet demand by potential students", 1),
            144 => array("Lack of skilled graduates from training & education institutes", 1),
            145 => array("Preference by skilled graduates to work in other (non-WASH) sectors (e.g. Mining, Transport, Construction) within the country", 1),
            146 => array("Emigration (temporary or permanent) of skilled workers to work abroad", 1),
            147 => array("Skilled workers do not want to live and work in rural areas of the country", 1),
            148 => array("Recruitment practices", 1),
            149 => array("Other (please specify)", 1),
            150 => array("Central government", 1),
            151 => array("Bilateral, multilateral donors", 1),
            152 => array("State, provincial, local", 1),
            153 => array("Tariffs and charges", 1),
            154 => array("Households out-of-pocket", 1),
            155 => array("NGOs, Lendors, Other", 1),
            160 => array("Household tariffs for services provided", 1, 170),
            161 => array("Household out-of-pocket expenditure for self-supply", 1, 171),
            162 => array("Government or public authority at Central level", 1, 172),
            163 => array("Government or public authority at State / provincial level", 1, 172),
            164 => array("Government or public authority at Local level", 1, 173),
            165 => array("External sources: International public transfers (ODA)", 1, 174),
            166 => array("External sources: Voluntary transfers (NGOs and foundations)", 1, 175),
            1105 => array("Drinking-water in schools", 1),
            1106 => array("Drinking-water in health facilities", 1)
        ),
        'Tables_S' => array(
            1 => array("Sanitation", null),
            9 => array("Level of development and implementation of policy / plan", 1), // kind of unique parent to avoid conflits between other Sanitation filters and this one
            10 => array("Sanitation", 9),
            11 => array("Target coverage", 10),
            12 => array("Target year", 10),
            13 => array("Level of development / Implementation of plan", 10),
            20 => array("Sanitation in schools", 9),
            21 => array("Target coverage", 20),
            22 => array("Target year", 20),
            23 => array("Level of development / Implementation of plan", 20),
            30 => array("Sanitation in health facilities", 9),
            31 => array("Target coverage", 30),
            32 => array("Target year", 30),
            33 => array("Level of development / Implementation of plan", 30),
            40 => array("To rehabilitate broken or disused public latrines  (e.g. in schools)", 1),
            41 => array("To safely empty or replace latrines when full ", 1),
            42 => array("To reuse wastewater and/or septage", 1),
            50 => array("Formal", 1),
            51 => array("Community-based", 1),
            52 => array("Informal", 1),
            53 => array("Self-supply", 1),
            60 => array("Ministry1", 1),
            61 => array("Govern/Regulate", 60),
            62 => array("Provide Service", 60),
            63 => array("Monitor/Survie", 60),
            70 => array("Ministry2", 1),
            71 => array("Govern/Regulate", 70),
            72 => array("Provide Service", 70),
            73 => array("Monitor/Survie", 70),
            80 => array("Ministry3", 1),
            81 => array("Govern/Regulate", 80),
            82 => array("Provide Service", 80),
            83 => array("Monitor/Survie", 80),
            90 => array("Ministry4", 1),
            91 => array("Govern/Regulate", 90),
            92 => array("Provide Service", 90),
            93 => array("Monitor/Survie", 90),
            100 => array("Ministry5", 1),
            101 => array("Govern/Regulate", 100),
            102 => array("Provide Service", 100),
            103 => array("Monitor/Survie", 100),
            110 => array("Poor populations", 1),
            111 => array("Populations living in slums or informal settlements", 1),
            112 => array("Remote or hard to reach areas	", 1),
            113 => array("Indigenous population", 1),
            114 => array("Internally displaced", 1),
            115 => array("Ethnic minorities", 1),
            116 => array("People with disabilities", 1),
            117 => array("Other disadvantaged groups", 1),
            118 => array("Other disadvantaged groups", 1),
            120 => array("Expenditure", 1),
            121 => array("Service quality", 1),
            122 => array("Equitable Service", 1),
            123 => array("Cost Effectiveness", 1),
            124 => array("Functionality of systems", 1),
            125 => array("Affordability", 1),
            126 => array("Wastewater/septage reuse", 1),
            127 => array("Institutional effectiveness", 1),
            128 => array("Cost-Recovery", 1),
            130 => array("Policy and strategy", 1),
            131 => array("Resource allocation", 1),
            140 => array("WASH HR strategy exists", 1),
            141 => array("Current difficulties in HR for WASH", 1),
            142 => array("Financial resources available for staff (salaries and benefits, including pensions etc.) ", 1),
            143 => array("Insufficient education/training organisations or courses to meet demand by potential students", 1),
            144 => array("Lack of skilled graduates from training & education institutes", 1),
            145 => array("Preference by skilled graduates to work in other (non-WASH) sectors (e.g. Mining, Transport, Construction) within the country", 1),
            146 => array("Emigration (temporary or permanent) of skilled workers to work abroad", 1),
            147 => array("Skilled workers do not want to live and work in rural areas of the country", 1),
            148 => array("Recruitment practices", 1),
            149 => array("Other (please specify)", 1),
            160 => array("Household tariffs for services provided", 1, 170),
            161 => array("Household out-of-pocket expenditure for self-supply", 1, 171),
            162 => array("Government or public authority at Central level", 1, 172),
            163 => array("Government or public authority at State / provincial level", 1, 172),
            164 => array("Government or public authority at Local level", 1, 173),
            165 => array("External sources: International public transfers (ODA)", 1, 174),
            166 => array("External sources: Voluntary transfers (NGOs and foundations)", 1, 175),
        //            1119 => array("Ministry of health", 1),
        //            1120 => array("National Water and sanitation authority (urban)", 1),
        //            1121 => array("Ministry of Municipalities (rural)", 1),
        //            1122 => array("Ministry of Education", 1),
        ),
        'Tables_H' => array(
            1 => array("Hygiene", null),
            9 => array("Level of development and implementation of policy / plan", 1), // kind of unique parent to avoid conflits between other Hygiene promotio filters and this one
            10 => array("Hygiene promotion", 9),
            11 => array("Target coverage", 10),
            12 => array("Target year", 10),
            13 => array("Level of development / Implementation of plan", 10),
            20 => array("Hygiene promotion in schools", 9),
            21 => array("Target coverage", 20),
            22 => array("Target year", 20),
            23 => array("Level of development / Implementation of plan", 20),
            30 => array("Hygiene promotion in health facilities", 9),
            31 => array("Target coverage", 30),
            32 => array("Target year", 30),
            33 => array("Level of development / Implementation of plan", 30),
            60 => array("Ministry1", 1),
            61 => array("Govern/Regulate", 60),
            62 => array("Provide Service", 60),
            63 => array("Monitor/Survie", 60),
            70 => array("Ministry2", 1),
            71 => array("Govern/Regulate", 70),
            72 => array("Provide Service", 70),
            73 => array("Monitor/Survie", 70),
            80 => array("Ministry3", 1),
            81 => array("Govern/Regulate", 80),
            82 => array("Provide Service", 80),
            83 => array("Monitor/Survie", 80),
            90 => array("Ministry4", 1),
            91 => array("Govern/Regulate", 90),
            92 => array("Provide Service", 90),
            93 => array("Monitor/Survie", 90),
            100 => array("Ministry5", 1),
            101 => array("Govern/Regulate", 100),
            102 => array("Provide Service", 100),
            103 => array("Monitor/Survie", 100),
            110 => array("Poor populations", 1),
            111 => array("Populations living in slums or informal settlements", 1),
            112 => array("Remote or hard to reach areas	", 1),
            113 => array("Indigenous population", 1),
            114 => array("Internally displaced", 1),
            115 => array("Ethnic minorities", 1),
            116 => array("People with disabilities", 1),
            117 => array("Other disadvantaged groups", 1),
            118 => array("Other disadvantaged groups", 1),
            140 => array("WASH HR strategy exists", 1),
            141 => array("Current difficulties in HR for WASH", 1),
            142 => array("Financial resources available for staff (salaries and benefits, including pensions etc.) ", 1),
            143 => array("Insufficient education/training organisations or courses to meet demand by potential students", 1),
            144 => array("Lack of skilled graduates from training & education institutes", 1),
            145 => array("Preference by skilled graduates to work in other (non-WASH) sectors (e.g. Mining, Transport, Construction) within the country", 1),
            146 => array("Emigration (temporary or permanent) of skilled workers to work abroad", 1),
            147 => array("Skilled workers do not want to live and work in rural areas of the country", 1),
            148 => array("Recruitment practices", 1),
            149 => array("Other (please specify)", 1),
            160 => array("Household tariffs for services provided", 1, 170),
            161 => array("Household out-of-pocket expenditure for self-supply", 1, 171),
            162 => array("Government or public authority at Central level", 1, 172),
            163 => array("Government or public authority at State / provincial level", 1, 172),
            164 => array("Government or public authority at Local level", 1, 173),
            165 => array("External sources: International public transfers (ODA)", 1, 174),
            166 => array("External sources: Voluntary transfers (NGOs and foundations)", 1, 175),
//            1127 => array("Ministry of health", 1),
//            1128 => array("National Water and sanitation authority (urban)", 1),
//            1129 => array("Ministry of Municipalities (rural)", 1),
//            1130 => array("Ministry of Education", 1),
        ),
        'Tables_U' => array(
            1 => array("Undefined", null),
            40 => array("To address resilience to climate change", 1),
            110 => array("Poor populations", 1),
            111 => array("Populations living in slums or informal settlements", 1),
            112 => array("Remote or hard to reach areas	", 1),
            113 => array("Indigenous population", 1),
            114 => array("Internally displaced", 1),
            115 => array("Ethnic minorities", 1),
            116 => array("People with disabilities", 1),
            117 => array("Other disadvantaged groups", 1),
            118 => array("Other disadvantaged groups", 1),
            130 => array("Identify public health priorities", 1),
            131 => array("Response to WASH related disease outbreak", 1),
            160 => array("Household tariffs for services provided", 1, 170),
            161 => array("Household out-of-pocket expenditure for self-supply", 1, 171),
            162 => array("Government or public authority at Central level", 1, 172),
            163 => array("Government or public authority at State / provincial level", 162, 172),
            164 => array("Government or public authority at Local level", 162, 173),
            165 => array("External sources: International public transfers (ODA)", 1, 174),
            166 => array("External sources: Voluntary transfers (NGOs and foundations)", 1, 175),
            // totals
            170 => array("Central Government", 1),
            171 => array("Bilateral, multilateral donors", 1),
            172 => array("State, provincial, local", 1),
            173 => array("Tariffs and charges", 1),
            174 => array("Households’ out-of-pocket", 1),
            175 => array("NGOs, Lendors, Other", 1),
//            11119 => array("Universal access for disadvantaged groups", 1),
//            11110 => array("Overall coordination between WASH actors:", 1),
        ),
    );

    /**
     * New filters for questions
     * @var array [table, filterIndex, questionId]
     */
    private $questions = [
        // Level of development and implementation of policy / plan
        // drinking water supply
        // target coverage
        ['Tables_W', 11, 154], // urban
        ['Tables_W', 11, 159], // rural
        // target year
        ['Tables_W', 12, 157], // urban
        ['Tables_W', 12, 162], // rural
        // Level of development / Implementation of plan
        ['Tables_W', 13, 59],
        // drinking water in schools
        ['Tables_W', 21, 164], // target coverage
        ['Tables_W', 22, 167], // target year
        ['Tables_W', 23, 60], // Level of development / Implementation of plan
        // drinking water in health facilities
        ['Tables_W', 31, 169], // target coverage
        ['Tables_W', 32, 172], // target year
        ['Tables_W', 33, 61], // Level of development / Implementation of plan
        // Sanitation
        // target coverage
        ['Tables_S', 11, 55], // urban
        ['Tables_S', 11, 69], // rural
        // target year
        ['Tables_S', 12, 58], // urban
        ['Tables_S', 12, 72], // rural
        // Level of development / Implementation of plan
        ['Tables_S', 13, 19],
        // sanitation in schools
        ['Tables_S', 21, 77], // target coverage
        ['Tables_S', 22, 80], // target year
        ['Tables_S', 23, 20], // Level of development / Implementation of plan
        // sanitation in health facilities
        ['Tables_S', 31, 97], // target coverage
        ['Tables_S', 32, 100], // target year
        ['Tables_S', 33, 21], // Level of development / Implementation of plan
        // Hygiene promotion
        ['Tables_H', 11, 174], // target coverage
        ['Tables_H', 12, 177], // target year
        ['Tables_H', 13, 62], // Level of development / Implementation of plan
        // Hygiene promotion in schools
        ['Tables_H', 21, 474], // target coverage
        ['Tables_H', 22, 522], // target year
        ['Tables_H', 23, 63], // Level of development / Implementation of plan
        // Hygiene promotion in health facilities
        ['Tables_H', 31, 478], // target coverage
        ['Tables_H', 32, 481], // target year
        ['Tables_H', 33, 64], // Level of development / Implementation of plan
        // Elements of sustainability in policy and level of implementation
        ['Tables_W', 40, 83],
        ['Tables_W', 41, 178],
        ['Tables_S', 40, 179],
        ['Tables_S', 41, 181],
        ['Tables_S', 42, 183],
        ['Tables_W', 42, 184],
        ['Tables_U', 40, 185],
        // Distribution of service provision for drinking-water by institutional type
        ['Tables_W', 50, 37],
        ['Tables_W', 51, 39],
        ['Tables_W', 52, 41],
        ['Tables_W', 53, 43],
        // Distribution of service provision for sanitation by institutional type
        ['Tables_S', 50, 36],
        ['Tables_S', 51, 38],
        ['Tables_S', 52, 40],
        ['Tables_S', 53, 42],
        // Level of WASH responsibilities of Ministries or National Institutions
        // ministry 1
        ['Tables_W', 60, 490], // ministry name
        ['Tables_W', 61, 491], // govern/regulate
        ['Tables_W', 62, 492], // provide service
        ['Tables_W', 63, 493], // monitor/survie
        ['Tables_S', 61, 500], // govern/regulate
        ['Tables_S', 62, 495], // provide service
        ['Tables_S', 63, 496], // monitor/survie
        ['Tables_H', 61, 497], // govern/regulate
        ['Tables_H', 62, 498], // provide service
        ['Tables_H', 63, 499], // monitor/survie
        // ministry 2
        ['Tables_W', 70, 501], // ministry name
        ['Tables_W', 71, 502], // govern/regulate
        ['Tables_W', 72, 503], // provide service
        ['Tables_W', 73, 504], // monitor/survie
        ['Tables_S', 71, 505], // govern/regulate
        ['Tables_S', 72, 506], // provide service
        ['Tables_S', 73, 507], // monitor/survie
        ['Tables_H', 71, 508], // govern/regulate
        ['Tables_H', 72, 509], // provide service
        ['Tables_H', 73, 510], // monitor/survie
        // ministry 3
        ['Tables_W', 80, 511], // ministry name
        ['Tables_W', 81, 512], // govern/regulate
        ['Tables_W', 82, 514], // provide service
        ['Tables_W', 83, 515], // monitor/survie
        ['Tables_S', 81, 516], // govern/regulate
        ['Tables_S', 82, 517], // provide service
        ['Tables_S', 83, 518], // monitor/survie
        ['Tables_H', 81, 519], // govern/regulate
        ['Tables_H', 82, 520], // provide service
        ['Tables_H', 83, 521], // monitor/survie
        // ministry 4
        ['Tables_W', 90, 523], // ministry name
        ['Tables_W', 91, 524], // govern/regulate
        ['Tables_W', 92, 525], // provide service
        ['Tables_W', 93, 526], // monitor/survie
        ['Tables_H', 91, 527], // govern/regulate
        ['Tables_H', 92, 528], // provide service
        ['Tables_H', 93, 529], // monitor/survie
        ['Tables_S', 91, 530], // govern/regulate
        ['Tables_S', 92, 531], // provide service
        ['Tables_S', 93, 532], // monitor/survie
        // ministry 5
        ['Tables_W', 100, 537], // ministry name
        ['Tables_W', 101, 533], // govern/regulate
        ['Tables_W', 102, 534], // provide service
        ['Tables_W', 103, 535], // monitor/survie
        ['Tables_H', 101, 536], // govern/regulate
        ['Tables_H', 102, 538], // provide service
        ['Tables_H', 103, 539], // monitor/survie
        ['Tables_S', 101, 540], // govern/regulate
        ['Tables_S', 102, 541], // provide service
        ['Tables_S', 103, 542], // monitor/survie
        // Disadvantaged groups identified in WASH plan and groups with monitoring systems
        // undefined
        ['Tables_U', 110, 102],
        ['Tables_U', 111, 104],
        ['Tables_U', 112, 106],
        ['Tables_U', 113, 108],
        ['Tables_U', 114, 110],
        ['Tables_U', 115, 112],
        ['Tables_U', 116, 114],
        ['Tables_U', 117, 116], // other disavantaged groups name (different from water+sanitation+hygiene)
        ['Tables_U', 118, 117],
        // sanitation
        ['Tables_S', 110, 219],
        ['Tables_S', 111, 220],
        ['Tables_S', 112, 221],
        ['Tables_S', 113, 222],
        ['Tables_S', 114, 223],
        ['Tables_S', 115, 224],
        ['Tables_S', 116, 225],
        ['Tables_S', 117, 227], // other disavantaged groups name (same for sanitation+water+hygiene) and different from undefined)
        ['Tables_S', 118, 226],
        // water
        ['Tables_W', 110, 452],
        ['Tables_W', 111, 454],
        ['Tables_W', 112, 456],
        ['Tables_W', 113, 458],
        ['Tables_W', 114, 460],
        ['Tables_W', 115, 462],
        ['Tables_W', 116, 464],
        ['Tables_W', 117, 227], // other disavantaged groups name
        ['Tables_W', 118, 466],
        // hygiene
        ['Tables_S', 110, 453],
        ['Tables_S', 111, 455],
        ['Tables_S', 112, 457],
        ['Tables_S', 113, 459],
        ['Tables_S', 114, 461],
        ['Tables_S', 115, 463],
        ['Tables_S', 116, 465],
        ['Tables_S', 117, 227], // other disavantaged groups name
        ['Tables_S', 118, 467],
        // Existence and implementation of performance indicators
        // sanitation
        ['Tables_S', 120, 239],
        ['Tables_S', 121, 240],
        ['Tables_S', 122, 243],
        ['Tables_S', 123, 242],
        ['Tables_S', 124, 244],
        ['Tables_S', 125, 245],
        ['Tables_S', 126, 246],
        ['Tables_S', 127, 248],
        ['Tables_S', 128, 249],
        // water
        ['Tables_W', 120, 252],
        ['Tables_W', 121, 254],
        ['Tables_W', 122, 256],
        ['Tables_W', 123, 259],
        ['Tables_W', 124, 258],
        ['Tables_W', 125, 262],
        // ['Tables_W', 126, 265], // not for water
        ['Tables_W', 127, 265],
        ['Tables_W', 128, 267],
        // Data availability
        // health sector (undefined)
        ['Tables_U', 130, 209],
        ['Tables_U', 131, 210],
        // sanitation
        ['Tables_S', 130, 211],
        ['Tables_S', 131, 212],
        // water
        ['Tables_W', 130, 213],
        ['Tables_W', 132, 214],
        ['Tables_W', 131, 215],
        // Financial flows
        // water
        ['Tables_W', 160, 561],
        ['Tables_W', 161, 562],
        ['Tables_W', 162, 563],
        ['Tables_W', 163, 564],
        ['Tables_W', 164, 565],
        ['Tables_W', 165, 566],
        ['Tables_W', 166, 567],
        // sanitation
        ['Tables_S', 160, 569],
        ['Tables_S', 161, 570],
        ['Tables_S', 162, 571],
        ['Tables_S', 163, 572],
        ['Tables_S', 164, 573],
        ['Tables_S', 165, 574],
        ['Tables_S', 166, 575],
        // hygiene
        ['Tables_H', 160, 577],
        ['Tables_H', 161, 578],
        ['Tables_H', 162, 579],
        ['Tables_H', 163, 580],
        ['Tables_H', 164, 581],
        ['Tables_H', 165, 582],
        ['Tables_H', 166, 583],
        // undefined
        ['Tables_H', 160, 585],
        ['Tables_H', 161, 586],
        ['Tables_H', 162, 587],
        ['Tables_H', 163, 588],
        ['Tables_H', 164, 589],
        ['Tables_H', 165, 590],
        ['Tables_H', 166, 591],
    ];

    public function import()
    {
        $this->partUrban = $this->getEntityManager()->getRepository('Application\Model\Part')->getOrCreate('Urban');
        $this->partRural = $this->getEntityManager()->getRepository('Application\Model\Part')->getOrCreate('Rural');
        $this->partTotal = $this->getEntityManager()->getRepository('Application\Model\Part')->getOrCreate('Total');

        $this->partOffsets = array(
            3 => $this->partUrban,
            4 => $this->partRural,
            5 => $this->partTotal,
        );

        $filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');
        $undefinedFilterSet = $filterRepository->findOneById(4);
        $undefinedFilterSet->setName('Undefined');

        // import filters
        $result = '';
        foreach ($this->filters as $table => $data) {
            $result .= $this->importGlassFilters($table);
            $this->createFirstFilterSet($this->cacheFiltersGlass[$table]);
            echo '.';
        }
        echo PHP_EOL;

        // import filtersets
        $filterSetRepository = $this->getEntityManager()->getRepository('Application\Model\FilterSet');
        foreach ($this->filterSets as $filterSetData) {
            $filterSetName = $filterSetData[0];
            $filterSet = $filterSetRepository->getOrCreate($filterSetName);
            $this->linkFilterSetToFilters($filterSet, $filterSetData[1]);

            echo '.';
        }
        echo PHP_EOL;

        $this->linkFiltersAcrossTables();

        $this->linkQuestions();
        $this->getEntityManager()->flush();

        return $result . "
Questions linked             : $this->linkedQuestionCount
Filters across tables linked : $this->linkedFilterAcrossTableCount
FilterSets linked            : $this->linkedFilterSetCount
";
    }

    /**
     * Import filters
     *
     * @param string $table
     */
    private function importGlassFilters($table)
    {
        // Import jmp filters
        if (isset($this->definitions[$table])) {
            $this->importFilters($this->definitions[$table]);
            $this->createFirstFilterSet($this->cacheFilters);
        }

        // Import glass filters
        foreach ($this->filters[$table] as $row => $definition) {
            $filter = $this->getFilter($definition, $this->cacheFiltersGlass[$table]);
            $this->cacheFiltersGlass[$table][$row] = $filter;
        }

        return count($this->cacheFiltersGlass[$table]) . ' glass filters imported' . PHP_EOL;
    }

    private function createFirstFilterSet(array $cache)
    {
        $firstFilter = reset($cache);
        $filterSetRepository = $this->getEntityManager()->getRepository('Application\Model\FilterSet');
        $filterSet = $filterSetRepository->getOrCreate($firstFilter->getName());
        foreach ($firstFilter->getChildren() as $child) {
            $filterSet->addFilter($child);
        }
    }

    /**
     * Link financial totals filters accross tables: Water / sanitation / sanitation / undefined
     */
    private function linkFiltersAcrossTables()
    {
        foreach ($this->filters as $table => $definitions) {
            foreach ($definitions as $filterIndex => $definition) {
                if (isset($definition[2])) {
                    $additionalParentIndex = $definition[2];
                    $this->cacheFiltersGlass['Tables_U'][$additionalParentIndex]->addChild($this->cacheFiltersGlass[$table][$filterIndex]);
                    $this->linkedFilterAcrossTableCount++;
                }
            }
        }
    }

    /**
     * Affects filters to a FilterSet
     * @param \Application\Model\FilterSet $filterSet
     * @param array $filters
     */
    private function linkFilterSetToFilters(FilterSet $filterSet, array $filters)
    {
        foreach ($filters as $table => $filterIndexes) {
            foreach ($filterIndexes as $index) {
                $filterSet->addFilter($this->cacheFiltersGlass[$table][$index]);
                $this->linkedFilterSetCount++;
            }
        }
    }

    /**
     * Links existing questions to newly created filters
     */
    private function linkQuestions()
    {
        $questionRepository = $this->getEntityManager()->getRepository('Application\Model\Question\AbstractAnswerableQuestion');
        foreach ($this->questions as $data) {
            list($table, $filterIndex, $questionId) = $data;

            $q = $questionRepository->findOneById($questionId);
            $q->setFilter($this->cacheFiltersGlass[$table][$filterIndex]);
            $this->linkedQuestionCount++;
        }
    }

}
