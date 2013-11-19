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

        'Tables_U' => array(
            4 => array("Undefined", null),
            5 => array("Central government", 4),
            6 => array("Bilateral, multilateral donors", 4),
            7 => array("State, provincial, local", 4),
            8 => array("Tariffs and charges", 4),
            9 => array("Households out-of-pocket", 4),
            10 => array("NGOs, Lendors, Other", 4),
            11 => array("total Central government", 4),
            12 => array("total Bilateral, multilateral donors", 4),
            13 => array("total State, provincial, local", 4),
            14 => array("total Tariffs and charges", 4),
            15 => array("total Households out-of-pocket", 4),
            16 => array("total NGOs, Lendors, Other", 4),
            17 => array("Identify public health priorities", 4),
            18 => array("Response to WASH related disease outbreak", 4),
            19 => array("Universal access for disadvantaged groups", 4),
            20 => array("Overall coordination between WASH actors:", 4),

        ),
        'Tables_H' => array(
            4 => array("Hygiene", null),
            5 => array("Central government", 4),
            6 => array("Bilateral, multilateral donors", 4),
            7 => array("State, provincial, local", 4),
            8 => array("Tariffs and charges", 4),
            9 => array("Households out-of-pocket", 4),
            10 => array("NGOs, Lendors, Other", 4),
            11 => array("WASH HR strategy exists", 4),
            12 => array("Current difficulties in HR for WASH", 4),
            13 => array("Financial resources available for staff (salaries and benefits, including pensions etc.) ", 4),
            14 => array("Insufficient education/training organisations or courses to meet demand by potential students", 4),
            15 => array("Lack of skilled graduates from training & education institutes", 4),
            16 => array("Preference by skilled graduates to work in other (non-WASH) sectors (e.g. Mining, Transport, Construction) within the country", 4),
            17 => array("Emigration (temporary or permanent) of skilled workers to work abroad", 4),
            18 => array("Skilled workers do not want to live and work in rural areas of the country", 4),
            19 => array("Recruitment practices", 4),
            20 => array("Other (please specify)", 4),
            21 => array("promotion", 4),
            22 => array("promotion in schools", 4),
            23 => array("promotion in health facilities", 4),
            24 => array("Govern/Regulate", 4),
            25 => array("Provide Service", 4),
            26 => array("Monitor/Survie", 4),
            27 => array("Ministry of health", 4),
            28 => array("National Water and sanitation authority (urban)", 4),
            29 => array("Ministry of Municipalities (rural)", 4),
            30 => array("Ministry of Education", 4),
            31 => array("Poor populations", 4),
            32 => array("Populations living in slums or informal settlements", 4),
            33 => array("Remote or hard to reach areas	", 4),
            34 => array("Indigenous population", 4),
            35 => array("Internally displaced", 4),
            36 => array("Ethnic minorities", 4),
            37 => array("People with disabilities", 4),
            38 => array("Other disadvantaged groups", 4),
            39 => array("To ensure drinking water quality meet national standards", 4),
            40 => array("To address resilience to climate change", 4),

        ),
        'Tables_W' => array(
            4  => array("Water", null),
            // Glass filters
            77  => array("Central government", 4),
            78  => array("Bilateral, multilateral donors", 4),
            79  => array("State, provincial, local", 4),
            80  => array("Tariffs and charges", 4),
            81  => array("Households out-of-pocket", 4),
            82  => array("NGOs, Lendors, Other", 4),
            83  => array("Expenditure", 4),
            84  => array("Service quality", 4),
            85  => array("Equitable Service", 4),
            86  => array("Cost Effectiveness", 4),
            87  => array("Functionality of systems", 4),
            88  => array("Affordability", 4),
            89  => array("Wastewater/septage reuse", 4),
            90  => array("Institutional effectiveness", 4),
            91  => array("Cost-Recovery", 4),
            92  => array("Policy and strategy", 4),
            93  => array("Resource allocation", 4),
            94  => array("National standards", 4),
            95  => array("WASH HR strategy exists", 4),
            96  => array("Current difficulties in HR for WASH", 4),
            97  => array("Financial resources available for staff (salaries and benefits, including pensions etc.) ", 4),
            98  => array("Insufficient education/training organisations or courses to meet demand by potential students", 4),
            99  => array("Lack of skilled graduates from training & education institutes", 4),
            100 => array("Preference by skilled graduates to work in other (non-WASH) sectors (e.g. Mining, Transport, Construction) within the country", 4),
            101 => array("Emigration (temporary or permanent) of skilled workers to work abroad", 4),
            102 => array("Skilled workers do not want to live and work in rural areas of the country", 4),
            103 => array("Recruitment practices", 4),
            104 => array("Other (please specify)", 4),
            105 => array("Drinking-water in schools", 4),
            106 => array("Drinking-water in health facilities", 4),
            107 => array("Formal", 4),
            108 => array("Community-based", 4),
            109 => array("Informal", 4),
            110 => array("Govern/Regulate", 4),
            111 => array("Provide Service", 4),
            112 => array("Monitor/Survie", 4),
            113 => array("Ministry of health", 4),
            114 => array("National Water and sanitation authority (urban)", 4),
            115 => array("Ministry of Municipalities (rural)", 4),
            116 => array("Ministry of Education", 4),
            117 => array("Poor populations", 4),
            118 => array("Populations living in slums or informal settlements", 4),
            119 => array("Remote or hard to reach areas	", 4),
            120 => array("Indigenous population", 4),
            121 => array("Internally displaced", 4),
            122 => array("Ethnic minorities", 4),
            123 => array("People with disabilities", 4),
            124 => array("Other disadvantaged groups", 4),
            125 => array("To keep rural water supplies functioning over the long-term", 4),
            126 => array("To improve the reliability and continuity of urban water supplies", 4),
            127 => array("Self-supply", 4),


        ),
        'Tables_S' => array(
            4  => array("Sanitation", null),

            // Glass filters
            84 => array("Sanitation Central government", 4),
            85 => array("Bilateral, multilateral donors", 4),
            86 => array("State, provincial, local", 4),
            87 => array("Tariffs and charges", 4),
            88 => array("Households out-of-pocket", 4),
            89 => array("NGOs, Lendors, Other", 4),
            90 => array("Expenditure", 4),
            91 => array("Service quality", 4),
            92 => array("Equitable Service", 4),
            93 => array("Cost Effectiveness", 4),
            94 => array("Functionality of systems", 4),
            95 => array("Affordability", 4),
            96 => array("Wastewater/septage reuse", 4),
            97 => array("Institutional effectiveness", 4),
            98 => array("Cost-Recovery", 4),
            99 => array("Policy and strategy", 4),
            100 => array("Resource allocation", 4),
            101 => array("WASH HR strategy exists", 4),
            102 => array("Current difficulties in HR for WASH", 4),
            103 => array("Financial resources available for staff (salaries and benefits, including pensions etc.) ", 4),
            104 => array("Insufficient education/training organisations or courses to meet demand by potential students", 4),
            105 => array("Lack of skilled graduates from training & education institutes", 4),
            106 => array("Preference by skilled graduates to work in other (non-WASH) sectors (e.g. Mining, Transport, Construction) within the country", 4),
            107 => array("Emigration (temporary or permanent) of skilled workers to work abroad", 4),
            108 => array("Skilled workers do not want to live and work in rural areas of the country", 4),
            109 => array("Recruitment practices", 4),
            110 => array("Other (please specify)", 4),
            111 => array("in schools", 4),
            112 => array("in health facilities", 4),
            113 => array("Formal", 4),
            114 => array("Community-based", 4),
            115 => array("Informal", 4),
            116 => array("Govern/Regulate", 4),
            117 => array("Provide Service", 4),
            118 => array("Monitor/Survie", 4),
            119 => array("Ministry of health", 4),
            120 => array("National Water and sanitation authority (urban)", 4),
            121 => array("Ministry of Municipalities (rural)", 4),
            122 => array("Ministry of Education", 4),
            123 => array("Poor populations", 4),
            124 => array("Populations living in slums or informal settlements", 4),
            125 => array("Remote or hard to reach areas	", 4),
            126 => array("Indigenous population", 4),
            127 => array("Internally displaced", 4),
            128 => array("Ethnic minorities", 4),
            129 => array("People with disabilities", 4),
            130 => array("Other disadvantaged groups", 4),
            131 => array("To rehabilitate broken or disused public latrines  (e.g. in schools)", 4),
            132 => array("To safely empty or replace latrines when full ", 4),
            133 => array("To reuse wastewater and/or septage", 4),
            134 => array("Self-supply", 4),
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

        $this->getEntityManager()->flush();
    }



    protected function linkFilters()
    {


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