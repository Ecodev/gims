<?php

namespace Application\Service\Importer;

use Application\Model\Filter;
use Application\Model\FilterSet;
use Application\Traits\EntityManagerAware;



class Glass extends AbstractImporter
{
    use EntityManagerAware;

    protected $cacheFiltersGlass = array(
        'Tables_W' => array(),
        'Tables_S' => array(),
        'Tables_H' => array(),
        'Tables_U' => array()
    );

    protected $filterSets = array(
        0 => 'Level of development and implementation of policy / plan',
        1 => 'Elements of sustainability in policy and level of implementation',
        2 => 'Level of WASH responsibilities of Ministries or National Institutions',
        3 => 'Distribution of service provision for drinking-water by institutional type',
        4 => 'Distribution of service provision for sanitation by institutional type',
        5 => 'Disadvantaged groups identified in WASH plan and groups with monitoring systems',
        6 => 'Existence and implementation of performance indicators',
        7 => 'Data availability	',
        8 => 'Existence of national assessment for WASH areas',
        9 => 'Human Resources',
        10 => 'Sufficiency of finance to meet MDG targets',
        11 => 'Absorption of funds',
        12 => 'Existence  and extent of implementation of Finance plan',
        13 => 'Funding Sources',
        14 => 'Urban vs. Rural Total WASH Expenditure',
        15 => 'WASH  Expenditure in Sanitation,  Drinking-water and Hygiene',
    );


    protected $filters = array(

        'Tables_W' => array(
            1  => array("Water", null),

            10 => array("Drinking-water supply", 1),
            11 => array("Target coverage", 10),
            12 => array("Target year", 10),
            13 => array("Level of development / Implementation of plan", 10),

            20 => array("Drinking-water in schools", 1),
            21 => array("Target coverage", 20),
            22 => array("Target year", 20),
            23 => array("Level of development / Implementation of plan", 20),

            30 => array("Drinking-water in health facilities", 1),
            31 => array("Target coverage", 30),
            32 => array("Target year", 30),
            33 => array("Level of development / Implementation of plan", 30),

			40 => array("To keep rural water supplies functioning over the long-term", 1),
			41 => array("To improve the reliability and continuity of urban water supplies", 1),            
            42 => array("To ensure drinking water quality meet national standards", 1),


            77  => array("Central government", 1),
            78  => array("Bilateral, multilateral donors", 1),
            79  => array("State, provincial, local", 1),
            80  => array("Tariffs and charges", 1),
            81  => array("Households out-of-pocket", 1),
            82  => array("NGOs, Lendors, Other", 1),
            83  => array("Expenditure", 1),
            84  => array("Service quality", 1),
            85  => array("Equitable Service", 1),
            86  => array("Cost Effectiveness", 1),
            87  => array("Functionality of systems", 1),
            88  => array("Affordability", 1),
            89  => array("Wastewater/septage reuse", 1),
            90  => array("Institutional effectiveness", 1),
            91  => array("Cost-Recovery", 1),
            92  => array("Policy and strategy", 1),
            93  => array("Resource allocation", 1),
            94  => array("National standards", 1),
            95  => array("WASH HR strategy exists", 1),
            96  => array("Current difficulties in HR for WASH", 1),
            97  => array("Financial resources available for staff (salaries and benefits, including pensions etc.) ", 1),
            98  => array("Insufficient education/training organisations or courses to meet demand by potential students", 1),
            99  => array("Lack of skilled graduates from training & education institutes", 1),
            100 => array("Preference by skilled graduates to work in other (non-WASH) sectors (e.g. Mining, Transport, Construction) within the country", 1),
            101 => array("Emigration (temporary or permanent) of skilled workers to work abroad", 1),
            102 => array("Skilled workers do not want to live and work in rural areas of the country", 1),
            103 => array("Recruitment practices", 1),
            104 => array("Other (please specify)", 1),
            105 => array("Drinking-water in schools", 1),
            106 => array("Drinking-water in health facilities", 1),
            107 => array("Formal", 1),
            108 => array("Community-based", 1),
            109 => array("Informal", 1),
            110 => array("Govern/Regulate", 1),
            111 => array("Provide Service", 1),
            112 => array("Monitor/Survie", 1),
            113 => array("Ministry of health", 1),
            114 => array("National Water and sanitation authority (urban)", 1),
            115 => array("Ministry of Municipalities (rural)", 1),
            116 => array("Ministry of Education", 1),
            117 => array("Poor populations", 1),
            118 => array("Populations living in slums or informal settlements", 1),
            119 => array("Remote or hard to reach areas	", 1),
            120 => array("Indigenous population", 1),
            121 => array("Internally displaced", 1),
            122 => array("Ethnic minorities", 1),
            123 => array("People with disabilities", 1),
            124 => array("Other disadvantaged groups", 1),

            127 => array("Self-supply", 1),

        ),
        'Tables_S' => array(
            1  => array("Sanitation", null),

            10 => array("Sanitation", 1),
            11 => array("Target coverage", 10),
            12 => array("Target year", 10),
            13 => array("Level of development / Implementation of plan", 10),

            20 => array("Sanitation in schools", 1),
            21 => array("Target coverage", 20),
            22 => array("Target year", 20),
            23 => array("Level of development / Implementation of plan", 20),

            30 => array("Sanitation in health facilities", 1),
            31 => array("Target coverage", 30),
            32 => array("Target year", 30),
            33 => array("Level of development / Implementation of plan", 30),
            
            40 => array("To rehabilitate broken or disused public latrines  (e.g. in schools)", 1),
            41 => array("To safely empty or replace latrines when full ", 1),
            42 => array("To reuse wastewater and/or septage", 1),



            84 => array("Sanitation Central government", 1),
            85 => array("Bilateral, multilateral donors", 1),
            86 => array("State, provincial, local", 1),
            87 => array("Tariffs and charges", 1),
            88 => array("Households out-of-pocket", 1),
            89 => array("NGOs, Lendors, Other", 1),
            90 => array("Expenditure", 1),
            91 => array("Service quality", 1),
            92 => array("Equitable Service", 1),
            93 => array("Cost Effectiveness", 1),
            94 => array("Functionality of systems", 1),
            95 => array("Affordability", 1),
            96 => array("Wastewater/septage reuse", 1),
            97 => array("Institutional effectiveness", 1),
            98 => array("Cost-Recovery", 1),
            99 => array("Policy and strategy", 1),
            100 => array("Resource allocation", 1),
            101 => array("WASH HR strategy exists", 1),
            102 => array("Current difficulties in HR for WASH", 1),
            103 => array("Financial resources available for staff (salaries and benefits, including pensions etc.) ", 1),
            104 => array("Insufficient education/training organisations or courses to meet demand by potential students", 1),
            105 => array("Lack of skilled graduates from training & education institutes", 1),
            106 => array("Preference by skilled graduates to work in other (non-WASH) sectors (e.g. Mining, Transport, Construction) within the country", 1),
            107 => array("Emigration (temporary or permanent) of skilled workers to work abroad", 1),
            108 => array("Skilled workers do not want to live and work in rural areas of the country", 1),
            109 => array("Recruitment practices", 1),
            110 => array("Other (please specify)", 1),

            113 => array("Formal", 1),
            114 => array("Community-based", 1),
            115 => array("Informal", 1),
            116 => array("Govern/Regulate", 1),
            117 => array("Provide Service", 1),
            118 => array("Monitor/Survie", 1),
            119 => array("Ministry of health", 1),
            120 => array("National Water and sanitation authority (urban)", 1),
            121 => array("Ministry of Municipalities (rural)", 1),
            122 => array("Ministry of Education", 1),
            123 => array("Poor populations", 1),
            124 => array("Populations living in slums or informal settlements", 1),
            125 => array("Remote or hard to reach areas	", 1),
            126 => array("Indigenous population", 1),
            127 => array("Internally displaced", 1),
            128 => array("Ethnic minorities", 1),
            129 => array("People with disabilities", 1),
            130 => array("Other disadvantaged groups", 1),
            134 => array("Self-supply", 1),


        ),
        'Tables_H' => array(
            1 => array("Hygiene", null),

            10 => array("Hygiene promotion", 1),
            11 => array("Target coverage", 10),
            12 => array("Target year", 10),
            13 => array("Level of development / Implementation of plan", 10),

            20 => array("Hygiene promotion in schools", 1),
            21 => array("Target coverage", 20),
            22 => array("Target year", 20),
            23 => array("Level of development / Implementation of plan", 20),

            30 => array("Hygiene promotion in health facilities", 1),
            31 => array("Target coverage", 30),
            32 => array("Target year", 30),
            33 => array("Level of development / Implementation of plan", 30),


            15 => array("Central government", 1),
            16 => array("Bilateral, multilateral donors", 1),
            17 => array("State, provincial, local", 1),
            18 => array("Tariffs and charges", 1),
            19 => array("Households out-of-pocket", 1),
            110 => array("NGOs, Lendors, Other", 1),
            111 => array("WASH HR strategy exists", 1),
            112 => array("Current difficulties in HR for WASH", 1),
            113 => array("Financial resources available for staff (salaries and benefits, including pensions etc.) ", 1),
            114 => array("Insufficient education/training organisations or courses to meet demand by potential students", 1),
            115 => array("Lack of skilled graduates from training & education institutes", 1),
            116 => array("Preference by skilled graduates to work in other (non-WASH) sectors (e.g. Mining, Transport, Construction) within the country", 1),
            117 => array("Emigration (temporary or permanent) of skilled workers to work abroad", 1),
            118 => array("Skilled workers do not want to live and work in rural areas of the country", 1),
            119 => array("Recruitment practices", 1),
            120 => array("Other (please specify)", 1),
            121 => array("promotion", 1),
            122 => array("promotion in schools", 1),
            123 => array("promotion in health facilities", 1),
            124 => array("Govern/Regulate", 1),
            125 => array("Provide Service", 1),
            126 => array("Monitor/Survie", 1),
            127 => array("Ministry of health", 1),
            128 => array("National Water and sanitation authority (urban)", 1),
            129 => array("Ministry of Municipalities (rural)", 1),
            130 => array("Ministry of Education", 1),
            131 => array("Poor populations", 1),
            132 => array("Populations living in slums or informal settlements", 1),
            133 => array("Remote or hard to reach areas	", 1),
            134 => array("Indigenous population", 1),
            135 => array("Internally displaced", 1),
            136 => array("Ethnic minorities", 1),
            137 => array("People with disabilities", 1),
            138 => array("Other disadvantaged groups", 1),
            
            

        ),
        'Tables_U' => array(
            1 => array("Undefined", null),
            
            40 => array("To address resilience to climate change", 1),
            
            5 => array("Central government", 1),
            6 => array("Bilateral, multilateral donors", 1),
            7 => array("State, provincial, local", 1),
            8 => array("Tariffs and charges", 1),
            9 => array("Households out-of-pocket", 1),
            10 => array("NGOs, Lendors, Other", 1),
            11 => array("total Central government", 1),
            12 => array("total Bilateral, multilateral donors", 1),
            13 => array("total State, provincial, local", 1),
            14 => array("total Tariffs and charges", 1),
            15 => array("total Households out-of-pocket", 1),
            16 => array("total NGOs, Lendors, Other", 1),
            17 => array("Identify public health priorities", 1),
            18 => array("Response to WASH related disease outbreak", 1),
            19 => array("Universal access for disadvantaged groups", 1),
            20 => array("Overall coordination between WASH actors:", 1),

        ),
    );

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

        $tables = array('Tables_W', 'Tables_S', 'Tables_H', 'Tables_U');

        $filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');
        $undefinedFilterSet = $filterRepository->findOneById(4);
        $undefinedFilterSet->setName('Undefined');


        // import filters
        foreach ($tables as $area) {
            $this->importFilters($area);
            $this->createFirstFilterSet($this->cacheFiltersGlass[$area]);
        }

        // import filtersets
        $filterSetRepository = $this->getEntityManager()->getRepository('Application\Model\FilterSet');

        foreach ($this->filterSets as $row => $filterSetName) {
            $filterSet = $filterSetRepository->getOrCreate($filterSetName);
            $this->cacheFilterSets[$row] = $filterSet;
        }

        $this->linkFilters();
        $this->linkQuestions();
        $this->getEntityManager()->flush();
    }



    /**
     * Import official filters
     *
     * @param array $officialFilters
     */
    protected function importFilters($area)
    {
        // import jmp filters
        if (isset($this->definitions[$area])) {
            $this->importOfficialFilters($this->definitions[$area]);
            $this->createFirstFilterSet($this->cacheFilters);
        }

        // Import glass filters
        foreach ($this->filters[$area] as $row => $definition) {
            $filter = $this->getFilter($definition);
            $this->cacheFiltersGlass[$area][$row] = $filter;
        }

        // Add all summands to filters
        foreach ($this->filters[$area] as $row => $definition) {
            $filter = $this->cacheFiltersGlass[$area][$row];
            if (isset($definition[3])) {
                $summands = $definition[3];
                if ($summands) {
                    foreach ($summands as $summand) {
                        $s = $this->cacheFiltersGlass[$area][$summand];
                        $filter->addSummand($s);
                    }
                }
            }
        }



        $this->getEntityManager()->flush();
        echo count($this->cacheFiltersGlass[$area]) . ' glass filters imported' . PHP_EOL;
    }


    protected function linkFilters()
    {
        // Level of development and implementation of policy / plan
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_H'][10]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_H'][11]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_H'][12]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_H'][13]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_H'][20]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_H'][21]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_H'][22]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_H'][23]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_H'][30]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_H'][31]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_H'][32]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_H'][33]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_S'][10]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_S'][11]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_S'][12]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_S'][13]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_S'][20]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_S'][21]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_S'][22]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_S'][23]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_S'][30]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_S'][31]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_S'][32]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_S'][33]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_W'][10]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_W'][11]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_W'][12]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_W'][13]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_W'][20]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_W'][21]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_W'][22]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_W'][23]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_W'][30]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_W'][31]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_W'][32]);
        $this->cacheFilterSets[0]->addFilter($this->cacheFiltersGlass['Tables_W'][33]);

        // Elements of sustainability in policy and level of implementation
        $this->cacheFilterSets[1]->addFilter($this->cacheFiltersGlass['Tables_W'][40]);
        $this->cacheFilterSets[1]->addFilter($this->cacheFiltersGlass['Tables_W'][41]);
        $this->cacheFilterSets[1]->addFilter($this->cacheFiltersGlass['Tables_S'][40]);
        $this->cacheFilterSets[1]->addFilter($this->cacheFiltersGlass['Tables_S'][41]);
        $this->cacheFilterSets[1]->addFilter($this->cacheFiltersGlass['Tables_S'][42]);
        $this->cacheFilterSets[1]->addFilter($this->cacheFiltersGlass['Tables_W'][42]);
        $this->cacheFilterSets[1]->addFilter($this->cacheFiltersGlass['Tables_U'][40]);

//
//        // Distribution of service provision for drinking-water by institutional type
//        $this->cacheFilterSets[3]->addFilter($this->cacheFiltersGlass['Tables_W'][106]);
//        $this->cacheFilterSets[3]->addFilter($this->cacheFiltersGlass['Tables_W'][107]);
//        $this->cacheFilterSets[3]->addFilter($this->cacheFiltersGlass['Tables_W'][108]);
//        $this->cacheFilterSets[3]->addFilter($this->cacheFiltersGlass['Tables_W'][127]);
//
//        // Distribution of service provision for sanitation by institutional type
//        $this->cacheFilterSets[4]->addFilter($this->cacheFiltersGlass['Tables_W'][113]);
//        $this->cacheFilterSets[4]->addFilter($this->cacheFiltersGlass['Tables_W'][114]);
//        $this->cacheFilterSets[4]->addFilter($this->cacheFiltersGlass['Tables_W'][115]);
//        $this->cacheFilterSets[4]->addFilter($this->cacheFiltersGlass['Tables_W'][134]);
    }


    protected function linkQuestions()
    {

        // Level of development and implementation of policy / plan
            // drinking water supply
                // target coverage
                $this->setFilter('W', 154 , 13); // urban
                $this->setFilter('W', 159 , 13); // rural

                // target year
                $this->setFilter('W', 157 , 13); // urban
                $this->setFilter('W', 162 , 13); // rural

                // Level of development / Implementation of plan
                $this->setFilter('W', 59 , 13);

            // drinking water in schools
            $this->setFilter('W', 164 , 13); // target coverage
            $this->setFilter('W', 167 , 13); // target year
            $this->setFilter('W', 60 , 13); // Level of development / Implementation of plan

            // drinking water in health facilities
            $this->setFilter('W', 169 , 13); // target coverage
            $this->setFilter('W', 172 , 13); // target year
            $this->setFilter('W', 61 , 13); // Level of development / Implementation of plan

            // Sanitation
                // target coverage
                $this->setFilter('S', 55 , 13); // urban
                $this->setFilter('S', 69 , 13); // rural

                // target year
                $this->setFilter('S', 58 , 13); // urban
                $this->setFilter('S', 72 , 13); // rural

                // Level of development / Implementation of plan
                $this->setFilter('S', 19 , 13);

                // sanitation in schools
                $this->setFilter('S', 77 , 13); // target coverage
                $this->setFilter('S', 80 , 13); // target year
                $this->setFilter('S', 20 , 13); // Level of development / Implementation of plan

                // sanitation in health facilities
                $this->setFilter('S', 97 , 13); // target coverage
                $this->setFilter('S', 100 , 13); // target year
                $this->setFilter('S', 21 , 13); // Level of development / Implementation of plan

            // Hygiene promotion
            $this->setFilter('H', 174 , 13); // target coverage
            $this->setFilter('S', 177 , 13); // target year
            $this->setFilter('H', 62 , 13); // Level of development / Implementation of plan

            // Hygiene promotion in schools
            $this->setFilter('H', 474 , 13); // target coverage
            $this->setFilter('H', 522 , 13); // target year
            $this->setFilter('H', 63 , 13); // Level of development / Implementation of plan

            // Hygiene promotion in health facilities
            $this->setFilter('H', 478 , 13); // target coverage
            $this->setFilter('H', 481 , 13); // target year
            $this->setFilter('H', 64 , 13); // Level of development / Implementation of plan

        // Elements of sustainability in policy and level of implementation
        $this->setFilter('W', 64 , 13);
        $this->setFilter('W', 64 , 13);
        $this->setFilter('S', 64 , 13);
        $this->setFilter('S', 64 , 13);
        $this->setFilter('S', 64 , 13);
        $this->setFilter('W', 64 , 13);
        $this->setFilter('U', 64 , 13);

    }

    protected function setFilter($area, $question_id, $filter_index)
    {
        /* @var $q \Application\Model\Question\AbstractAnswerableQuestion */
        $filterRepository = $this->getEntityManager()->getRepository('Application\Model\Question\AbstractAnswerableQuestion');

        $q = $filterRepository->findOneById($question_id);
        $q->setFilter($this->cacheFiltersGlass['Tables_'.$area][$filter_index]);
    }



    protected function createFirstFilterSet($cache)
    {
        $firstFilter = reset($cache);
        $filterSetRepository = $this->getEntityManager()->getRepository('Application\Model\FilterSet');
        $filterSet = $filterSetRepository->getOrCreate($firstFilter->getName());
        foreach ($firstFilter->getChildren() as $child) {
            if ($child->isOfficial()) {
                $filterSet->addFilter($child);
            }
        }
    }
}