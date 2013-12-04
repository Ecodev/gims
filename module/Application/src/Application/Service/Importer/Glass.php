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
        7 => 'Data availability',
//        8 => 'Human Resources',
	    9 => 'Funding Sources',

//        11 => 'Sufficiency of finance to meet MDG targets',
//        12 => 'Absorption of funds',
//        13 => 'Existence  and extent of implementation of Finance plan',
//        14 => 'WASH  Expenditure in Sanitation,  Drinking-water and Hygiene',
//        15 => 'Existence of national assessment for WASH areas',
        
    );


    protected $filters = array(

        'Tables_W' => array(
            1  => array("Water", null),

			9  => array("Level of development and implementation of policy / plan", 1), // kind of unique parent to avoid conflits between other Drinking water supply filters and this one
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
            
            120  => array("Expenditure", 1),
            121  => array("Service quality", 1),
            122  => array("Equitable Service", 1),
            123  => array("Cost Effectiveness", 1),
            124  => array("Functionality of systems", 1),
            125  => array("Affordability", 1),
            // 126  => array("Wastewater/septage reuse", 1), // not for water
            127  => array("Institutional effectiveness", 1),
            128  => array("Cost-Recovery", 1),
            
            130  => array("Policy and strategy", 1),
            131  => array("Resource allocation", 1),
            132  => array("National standards", 1),
            
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
            
            150  => array("Central government", 1),
            151  => array("Bilateral, multilateral donors", 1),
            152  => array("State, provincial, local", 1),
            153  => array("Tariffs and charges", 1),
            154  => array("Households out-of-pocket", 1),
            155  => array("NGOs, Lendors, Other", 1),

            160  => array("Household tariffs for services provided", 1),
            161  => array("Household out-of-pocket expenditure for self-supply", 1),
            162  => array("Government or public authority at Central level", 1),
            163  => array("Government or public authority at State / provincial level", 1),
            164  => array("Government or public authority at Local level", 1),
            165  => array("External sources: International public transfers (ODA)", 1),
            166  => array("External sources: Voluntary transfers (NGOs and foundations)", 1),

            1105 => array("Drinking-water in schools", 1),
            1106 => array("Drinking-water in health facilities", 1)


        ),
        'Tables_S' => array(
            1  => array("Sanitation", null),

			9  => array("Level of development and implementation of policy / plan", 1), // kind of unique parent to avoid conflits between other Sanitation filters and this one
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

            120  => array("Expenditure", 1),
            121  => array("Service quality", 1),
            122  => array("Equitable Service", 1),
            123  => array("Cost Effectiveness", 1),
            124  => array("Functionality of systems", 1),
            125  => array("Affordability", 1),
            126  => array("Wastewater/septage reuse", 1),
            127  => array("Institutional effectiveness", 1),
            128  => array("Cost-Recovery", 1),

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

            160  => array("Household tariffs for services provided", 1),
            161  => array("Household out-of-pocket expenditure for self-supply", 1),
            162  => array("Government or public authority at Central level", 1),
            163  => array("Government or public authority at State / provincial level", 1),
            164  => array("Government or public authority at Local level", 1),
            165  => array("External sources: International public transfers (ODA)", 1),
            166  => array("External sources: Voluntary transfers (NGOs and foundations)", 1),


//            1119 => array("Ministry of health", 1),
//            1120 => array("National Water and sanitation authority (urban)", 1),
//            1121 => array("Ministry of Municipalities (rural)", 1),
//            1122 => array("Ministry of Education", 1),

        ),
        'Tables_H' => array(
            1 => array("Hygiene", null),

			9  => array("Level of development and implementation of policy / plan", 1), // kind of unique parent to avoid conflits between other Hygiene promotio filters and this one
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

            160  => array("Household tariffs for services provided", 1),
            161  => array("Household out-of-pocket expenditure for self-supply", 1),
            162  => array("Government or public authority at Central level", 1),
            163  => array("Government or public authority at State / provincial level", 1),
            164  => array("Government or public authority at Local level", 1),
            165  => array("External sources: International public transfers (ODA)", 1),
            166  => array("External sources: Voluntary transfers (NGOs and foundations)", 1),


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

            160  => array("Household tariffs for services provided", 1),
            161  => array("Household out-of-pocket expenditure for self-supply", 1),
            162  => array("Government or public authority at Central level", 1),
            163  => array("Government or public authority at State / provincial level", 162),
            164  => array("Government or public authority at Local level", 162),
            165  => array("External sources: International public transfers (ODA)", 1),
            166  => array("External sources: Voluntary transfers (NGOs and foundations)", 1),

            // totals
            170  => array("Central Government", 1),
            171  => array("Bilateral, multilateral donors", 1),
            172  => array("State, provincial, local", 1),
            173  => array("Tariffs and charges", 1),
            174  => array("Households’ out-of-pocket", 1),
            175  => array("NGOs, Lendors, Other", 1),

//            11119 => array("Universal access for disadvantaged groups", 1),
//            11110 => array("Overall coordination between WASH actors:", 1),

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
        $this->linkFilterSets();
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
            $filter = $this->getFilter($definition, $this->cacheFiltersGlass[$area]);
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


    protected function updateQuestionFilter($area, $filter_index, $question_id)
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


    protected function linkFilters()
    {
        // Link financial totals filters -> accross tables Water / sanitation / sanitation / undefined


        $this->cacheFiltersGlass['Tables_U'][170]->addChild($this->cacheFiltersGlass['Tables_W'][160]);
        $this->cacheFiltersGlass['Tables_U'][171]->addChild($this->cacheFiltersGlass['Tables_W'][161]);
        $this->cacheFiltersGlass['Tables_U'][172]->addChild($this->cacheFiltersGlass['Tables_W'][162]);
        $this->cacheFiltersGlass['Tables_U'][172]->addChild($this->cacheFiltersGlass['Tables_W'][163]);
        $this->cacheFiltersGlass['Tables_U'][173]->addChild($this->cacheFiltersGlass['Tables_W'][164]);
        $this->cacheFiltersGlass['Tables_U'][174]->addChild($this->cacheFiltersGlass['Tables_W'][165]);
        $this->cacheFiltersGlass['Tables_U'][175]->addChild($this->cacheFiltersGlass['Tables_W'][166]);

        $this->cacheFiltersGlass['Tables_U'][170]->addChild($this->cacheFiltersGlass['Tables_S'][160]);
        $this->cacheFiltersGlass['Tables_U'][171]->addChild($this->cacheFiltersGlass['Tables_S'][161]);
        $this->cacheFiltersGlass['Tables_U'][172]->addChild($this->cacheFiltersGlass['Tables_S'][162]);
        $this->cacheFiltersGlass['Tables_U'][172]->addChild($this->cacheFiltersGlass['Tables_S'][163]);
        $this->cacheFiltersGlass['Tables_U'][173]->addChild($this->cacheFiltersGlass['Tables_S'][164]);
        $this->cacheFiltersGlass['Tables_U'][174]->addChild($this->cacheFiltersGlass['Tables_S'][165]);
        $this->cacheFiltersGlass['Tables_U'][175]->addChild($this->cacheFiltersGlass['Tables_S'][166]);

        $this->cacheFiltersGlass['Tables_U'][170]->addChild($this->cacheFiltersGlass['Tables_S'][160]);
        $this->cacheFiltersGlass['Tables_U'][171]->addChild($this->cacheFiltersGlass['Tables_S'][161]);
        $this->cacheFiltersGlass['Tables_U'][172]->addChild($this->cacheFiltersGlass['Tables_S'][162]);
        $this->cacheFiltersGlass['Tables_U'][172]->addChild($this->cacheFiltersGlass['Tables_S'][163]);
        $this->cacheFiltersGlass['Tables_U'][173]->addChild($this->cacheFiltersGlass['Tables_S'][164]);
        $this->cacheFiltersGlass['Tables_U'][174]->addChild($this->cacheFiltersGlass['Tables_S'][165]);
        $this->cacheFiltersGlass['Tables_U'][175]->addChild($this->cacheFiltersGlass['Tables_S'][166]);

        $this->cacheFiltersGlass['Tables_U'][170]->addChild($this->cacheFiltersGlass['Tables_U'][160]);
        $this->cacheFiltersGlass['Tables_U'][171]->addChild($this->cacheFiltersGlass['Tables_U'][161]);
        $this->cacheFiltersGlass['Tables_U'][172]->addChild($this->cacheFiltersGlass['Tables_U'][162]);
        $this->cacheFiltersGlass['Tables_U'][172]->addChild($this->cacheFiltersGlass['Tables_U'][163]);
        $this->cacheFiltersGlass['Tables_U'][173]->addChild($this->cacheFiltersGlass['Tables_U'][164]);
        $this->cacheFiltersGlass['Tables_U'][174]->addChild($this->cacheFiltersGlass['Tables_U'][165]);
        $this->cacheFiltersGlass['Tables_U'][175]->addChild($this->cacheFiltersGlass['Tables_U'][166]);

    }
    protected function linkFilterSets()
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

        //Level of WASH responsibilities of Ministries or National Institutions
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][60]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][61]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][62]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][63]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][70]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][71]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][72]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][73]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][80]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][81]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][82]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][83]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][90]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][91]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][92]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][93]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][100]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][101]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][102]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_W'][103]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][60]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][61]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][62]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][63]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][70]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][71]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][72]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][73]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][80]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][81]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][82]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][83]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][90]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][91]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][92]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][93]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][100]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][101]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][102]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_S'][103]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][60]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][61]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][62]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][63]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][70]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][71]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][72]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][73]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][80]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][81]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][82]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][83]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][90]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][91]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][92]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][93]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][100]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][101]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][102]);
        $this->cacheFilterSets[2]->addFilter($this->cacheFiltersGlass['Tables_H'][103]);

        // Distribution of service provision for drinking-water by institutional type
        $this->cacheFilterSets[3]->addFilter($this->cacheFiltersGlass['Tables_W'][50]);
        $this->cacheFilterSets[3]->addFilter($this->cacheFiltersGlass['Tables_W'][51]);
        $this->cacheFilterSets[3]->addFilter($this->cacheFiltersGlass['Tables_W'][52]);
        $this->cacheFilterSets[3]->addFilter($this->cacheFiltersGlass['Tables_W'][53]);

        // Distribution of service provision for sanitation by institutional type
        $this->cacheFilterSets[4]->addFilter($this->cacheFiltersGlass['Tables_S'][50]);
        $this->cacheFilterSets[4]->addFilter($this->cacheFiltersGlass['Tables_S'][51]);
        $this->cacheFilterSets[4]->addFilter($this->cacheFiltersGlass['Tables_S'][52]);
        $this->cacheFilterSets[4]->addFilter($this->cacheFiltersGlass['Tables_S'][53]);

        // Disadvantaged groups identified in WASH plan and groups with monitoring systems
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_W'][110]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_W'][111]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_W'][112]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_W'][113]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_W'][114]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_W'][115]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_W'][116]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_W'][117]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_W'][118]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_S'][110]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_S'][111]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_S'][112]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_S'][113]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_S'][114]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_S'][115]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_S'][116]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_S'][117]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_S'][118]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_H'][110]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_H'][111]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_H'][112]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_H'][113]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_H'][114]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_H'][115]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_H'][116]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_H'][117]);
        $this->cacheFilterSets[5]->addFilter($this->cacheFiltersGlass['Tables_H'][118]);

        // Existence and implementation of performance indicators
        $this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_S'][120]);
        $this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_S'][121]);
        $this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_S'][122]);
        $this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_S'][123]);
        $this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_S'][124]);
        $this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_S'][125]);
        $this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_S'][126]);
        $this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_S'][127]);
        $this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_S'][128]);
        $this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_W'][120]);
        $this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_W'][121]);
        $this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_W'][122]);
        $this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_W'][123]);
        $this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_W'][124]);
        $this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_W'][125]);
        //$this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_W'][126]);
        $this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_W'][127]);
        $this->cacheFilterSets[6]->addFilter($this->cacheFiltersGlass['Tables_W'][128]);

        // Data availability
        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_W'][130]);
        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_W'][131]);
        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_W'][132]);
        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_S'][130]);
        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_S'][131]);
        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_U'][130]);
        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_U'][131]);

        // Human Resources
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_W'][140]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_W'][141]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_W'][142]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_W'][143]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_W'][144]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_W'][145]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_W'][146]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_W'][147]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_W'][148]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_W'][149]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_S'][140]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_S'][141]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_S'][142]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_S'][143]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_S'][144]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_S'][145]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_S'][146]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_S'][147]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_S'][148]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_S'][149]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_H'][140]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_H'][141]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_H'][142]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_H'][143]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_H'][144]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_H'][145]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_H'][146]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_H'][147]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_H'][148]);
//        $this->cacheFilterSets[7]->addFilter($this->cacheFiltersGlass['Tables_H'][149]);


        // Financial flows
            // Funding Sources
            $this->cacheFilterSets[9]->addFilter($this->cacheFiltersGlass['Tables_U'][160]);
            $this->cacheFilterSets[9]->addFilter($this->cacheFiltersGlass['Tables_U'][161]);
            $this->cacheFilterSets[9]->addFilter($this->cacheFiltersGlass['Tables_U'][163]);
            $this->cacheFilterSets[9]->addFilter($this->cacheFiltersGlass['Tables_U'][164]);
            $this->cacheFilterSets[9]->addFilter($this->cacheFiltersGlass['Tables_U'][165]);
            $this->cacheFilterSets[9]->addFilter($this->cacheFiltersGlass['Tables_U'][166]);

    }

    protected function linkQuestions()
    {

        // Level of development and implementation of policy / plan
            // drinking water supply
                // target coverage
                $this->updateQuestionFilter('W', 11, 154); // urban
                $this->updateQuestionFilter('W', 11, 159); // rural

                // target year
                $this->updateQuestionFilter('W', 12, 157); // urban
                $this->updateQuestionFilter('W', 12, 162); // rural

                // Level of development / Implementation of plan
                $this->updateQuestionFilter('W', 13, 59);

            // drinking water in schools
            $this->updateQuestionFilter('W', 21, 164); // target coverage
            $this->updateQuestionFilter('W', 22, 167); // target year
            $this->updateQuestionFilter('W', 23, 60); // Level of development / Implementation of plan

            // drinking water in health facilities
            $this->updateQuestionFilter('W', 31, 169); // target coverage
            $this->updateQuestionFilter('W', 32, 172); // target year
            $this->updateQuestionFilter('W', 33, 61); // Level of development / Implementation of plan

            // Sanitation
                // target coverage
                $this->updateQuestionFilter('S', 11, 55); // urban
                $this->updateQuestionFilter('S', 11, 69); // rural

                // target year
                $this->updateQuestionFilter('S', 12, 58); // urban
                $this->updateQuestionFilter('S', 12, 72); // rural

                // Level of development / Implementation of plan
                $this->updateQuestionFilter('S', 13, 19);

                // sanitation in schools
                $this->updateQuestionFilter('S', 21, 77); // target coverage
                $this->updateQuestionFilter('S', 22, 80); // target year
                $this->updateQuestionFilter('S', 23, 20); // Level of development / Implementation of plan

                // sanitation in health facilities
                $this->updateQuestionFilter('S', 31, 97); // target coverage
                $this->updateQuestionFilter('S', 32, 100); // target year
                $this->updateQuestionFilter('S', 33, 21); // Level of development / Implementation of plan

            // Hygiene promotion
            $this->updateQuestionFilter('H', 11, 174); // target coverage
            $this->updateQuestionFilter('H', 12, 177); // target year
            $this->updateQuestionFilter('H', 13, 62); // Level of development / Implementation of plan

            // Hygiene promotion in schools
            $this->updateQuestionFilter('H', 21, 474); // target coverage
            $this->updateQuestionFilter('H', 22, 522); // target year
            $this->updateQuestionFilter('H', 23, 63); // Level of development / Implementation of plan

            // Hygiene promotion in health facilities
            $this->updateQuestionFilter('H', 31, 478); // target coverage
            $this->updateQuestionFilter('H', 32, 481); // target year
            $this->updateQuestionFilter('H', 33, 64); // Level of development / Implementation of plan

        // Elements of sustainability in policy and level of implementation
        $this->updateQuestionFilter('W', 40, 83);
        $this->updateQuestionFilter('W', 41, 178);
        $this->updateQuestionFilter('S', 40, 179);
        $this->updateQuestionFilter('S', 41, 181);
        $this->updateQuestionFilter('S', 42, 183);
        $this->updateQuestionFilter('W', 42, 184);
        $this->updateQuestionFilter('U', 40, 185);

        // Distribution of service provision for drinking-water by institutional type
        $this->updateQuestionFilter('W', 50, 37);
        $this->updateQuestionFilter('W', 51, 39);
        $this->updateQuestionFilter('W', 52, 41);
        $this->updateQuestionFilter('W', 53, 43);

        // Distribution of service provision for sanitation by institutional type
        $this->updateQuestionFilter('S', 50, 36);
        $this->updateQuestionFilter('S', 51, 38);
        $this->updateQuestionFilter('S', 52, 40);
        $this->updateQuestionFilter('S', 53, 42);

        // Level of WASH responsibilities of Ministries or National Institutions
            // ministry 1
            $this->updateQuestionFilter('W', 60, 490); // ministry name
            $this->updateQuestionFilter('W', 61, 491); // govern/regulate
            $this->updateQuestionFilter('W', 62, 492); // provide service
            $this->updateQuestionFilter('W', 63, 493); // monitor/survie
            $this->updateQuestionFilter('S', 61, 500); // govern/regulate
            $this->updateQuestionFilter('S', 62, 495); // provide service
            $this->updateQuestionFilter('S', 63, 496); // monitor/survie
            $this->updateQuestionFilter('H', 61, 497); // govern/regulate
            $this->updateQuestionFilter('H', 62, 498); // provide service
            $this->updateQuestionFilter('H', 63, 499); // monitor/survie

            // ministry 2
            $this->updateQuestionFilter('W', 70, 501); // ministry name
            $this->updateQuestionFilter('W', 71, 502); // govern/regulate
            $this->updateQuestionFilter('W', 72, 503); // provide service
            $this->updateQuestionFilter('W', 73, 504); // monitor/survie
            $this->updateQuestionFilter('S', 71, 505); // govern/regulate
            $this->updateQuestionFilter('S', 72, 506); // provide service
            $this->updateQuestionFilter('S', 73, 507); // monitor/survie
            $this->updateQuestionFilter('H', 71, 508); // govern/regulate
            $this->updateQuestionFilter('H', 72, 509); // provide service
            $this->updateQuestionFilter('H', 73, 510); // monitor/survie

            // ministry 3
            $this->updateQuestionFilter('W', 80, 511); // ministry name
            $this->updateQuestionFilter('W', 81, 512); // govern/regulate
            $this->updateQuestionFilter('W', 82, 514); // provide service
            $this->updateQuestionFilter('W', 83, 515); // monitor/survie
            $this->updateQuestionFilter('S', 81, 516); // govern/regulate
            $this->updateQuestionFilter('S', 82, 517); // provide service
            $this->updateQuestionFilter('S', 83, 518); // monitor/survie
            $this->updateQuestionFilter('H', 81, 519); // govern/regulate
            $this->updateQuestionFilter('H', 82, 520); // provide service
            $this->updateQuestionFilter('H', 83, 521); // monitor/survie

            // ministry 4
            $this->updateQuestionFilter('W', 90, 523); // ministry name
            $this->updateQuestionFilter('W', 91, 524); // govern/regulate
            $this->updateQuestionFilter('W', 92, 525); // provide service
            $this->updateQuestionFilter('W', 93, 526); // monitor/survie
            $this->updateQuestionFilter('H', 91, 527); // govern/regulate
            $this->updateQuestionFilter('H', 92, 528); // provide service
            $this->updateQuestionFilter('H', 93, 529); // monitor/survie
            $this->updateQuestionFilter('S', 91, 530); // govern/regulate
            $this->updateQuestionFilter('S', 92, 531); // provide service
            $this->updateQuestionFilter('S', 93, 532); // monitor/survie

            // ministry 5
            $this->updateQuestionFilter('W', 100, 537); // ministry name
            $this->updateQuestionFilter('W', 101, 533); // govern/regulate
            $this->updateQuestionFilter('W', 102, 534); // provide service
            $this->updateQuestionFilter('W', 103, 535); // monitor/survie
            $this->updateQuestionFilter('H', 101, 536); // govern/regulate
            $this->updateQuestionFilter('H', 102, 538); // provide service
            $this->updateQuestionFilter('H', 103, 539); // monitor/survie
            $this->updateQuestionFilter('S', 101, 540); // govern/regulate
            $this->updateQuestionFilter('S', 102, 541); // provide service
            $this->updateQuestionFilter('S', 103, 542); // monitor/survie


        // Disadvantaged groups identified in WASH plan and groups with monitoring systems
            // undefined
            $this->updateQuestionFilter('U', 110, 102);
            $this->updateQuestionFilter('U', 111, 104);
            $this->updateQuestionFilter('U', 112, 106);
            $this->updateQuestionFilter('U', 113, 108);
            $this->updateQuestionFilter('U', 114, 110);
            $this->updateQuestionFilter('U', 115, 112);
            $this->updateQuestionFilter('U', 116, 114);
            $this->updateQuestionFilter('U', 117, 116); // other disavantaged groups name (different from water+sanitation+hygiene)
            $this->updateQuestionFilter('U', 118, 117);

            // sanitation
            $this->updateQuestionFilter('S', 110, 219);
            $this->updateQuestionFilter('S', 111, 220);
            $this->updateQuestionFilter('S', 112, 221);
            $this->updateQuestionFilter('S', 113, 222);
            $this->updateQuestionFilter('S', 114, 223);
            $this->updateQuestionFilter('S', 115, 224);
            $this->updateQuestionFilter('S', 116, 225);
            $this->updateQuestionFilter('S', 117, 227); // other disavantaged groups name (same for sanitation+water+hygiene) and different from undefined)
            $this->updateQuestionFilter('S', 118, 226);

            // water
            $this->updateQuestionFilter('W', 110, 452);
            $this->updateQuestionFilter('W', 111, 454);
            $this->updateQuestionFilter('W', 112, 456);
            $this->updateQuestionFilter('W', 113, 458);
            $this->updateQuestionFilter('W', 114, 460);
            $this->updateQuestionFilter('W', 115, 462);
            $this->updateQuestionFilter('W', 116, 464);
            $this->updateQuestionFilter('W', 117, 227); // other disavantaged groups name
            $this->updateQuestionFilter('W', 118, 466);

            // hygiene
            $this->updateQuestionFilter('S', 110, 453);
            $this->updateQuestionFilter('S', 111, 455);
            $this->updateQuestionFilter('S', 112, 457);
            $this->updateQuestionFilter('S', 113, 459);
            $this->updateQuestionFilter('S', 114, 461);
            $this->updateQuestionFilter('S', 115, 463);
            $this->updateQuestionFilter('S', 116, 465);
            $this->updateQuestionFilter('S', 117, 227); // other disavantaged groups name
            $this->updateQuestionFilter('S', 118, 467);

            // Existence and implementation of performance indicators
                // sanitation
                $this->updateQuestionFilter('S', 120, 239);
                $this->updateQuestionFilter('S', 121, 240);
                $this->updateQuestionFilter('S', 122, 243);
                $this->updateQuestionFilter('S', 123, 242);
                $this->updateQuestionFilter('S', 124, 244);
                $this->updateQuestionFilter('S', 125, 245);
                $this->updateQuestionFilter('S', 126, 246);
                $this->updateQuestionFilter('S', 127, 248);
                $this->updateQuestionFilter('S', 128, 249);

                // water
                $this->updateQuestionFilter('W', 120, 252);
                $this->updateQuestionFilter('W', 121, 254);
                $this->updateQuestionFilter('W', 122, 256);
                $this->updateQuestionFilter('W', 123, 259);
                $this->updateQuestionFilter('W', 124, 258);
                $this->updateQuestionFilter('W', 125, 262);
                // $this->updateQuestionFilter('W', 126, 265); // not for water
                $this->updateQuestionFilter('W', 127, 265);
                $this->updateQuestionFilter('W', 128, 267);

        // Data availability
            // health sector (undefined)
            $this->updateQuestionFilter('U', 130, 209);
            $this->updateQuestionFilter('U', 131, 210);

            // sanitation
            $this->updateQuestionFilter('S', 130, 211);
            $this->updateQuestionFilter('S', 131, 212);

            // water
            $this->updateQuestionFilter('W', 130, 213);
            $this->updateQuestionFilter('W', 132, 214);
            $this->updateQuestionFilter('W', 131, 215);

        // Financial flows
            // water
            $this->updateQuestionFilter('W', 160, 561);
            $this->updateQuestionFilter('W', 161, 562);
            $this->updateQuestionFilter('W', 162, 563);
            $this->updateQuestionFilter('W', 163, 564);
            $this->updateQuestionFilter('W', 164, 565);
            $this->updateQuestionFilter('W', 165, 566);
            $this->updateQuestionFilter('W', 166, 567);

            // sanitation
            $this->updateQuestionFilter('S', 160, 569);
            $this->updateQuestionFilter('S', 161, 570);
            $this->updateQuestionFilter('S', 162, 571);
            $this->updateQuestionFilter('S', 163, 572);
            $this->updateQuestionFilter('S', 164, 573);
            $this->updateQuestionFilter('S', 165, 574);
            $this->updateQuestionFilter('S', 166, 575);

            // hygiene
            $this->updateQuestionFilter('H', 160, 577);
            $this->updateQuestionFilter('H', 161, 578);
            $this->updateQuestionFilter('H', 162, 579);
            $this->updateQuestionFilter('H', 163, 580);
            $this->updateQuestionFilter('H', 164, 581);
            $this->updateQuestionFilter('H', 165, 582);
            $this->updateQuestionFilter('H', 166, 583);

            // undefined
            $this->updateQuestionFilter('H', 160, 585);
            $this->updateQuestionFilter('H', 161, 586);
            $this->updateQuestionFilter('H', 162, 587);
            $this->updateQuestionFilter('H', 163, 588);
            $this->updateQuestionFilter('H', 164, 589);
            $this->updateQuestionFilter('H', 165, 590);
            $this->updateQuestionFilter('H', 166, 591);

    }
}