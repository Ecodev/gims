<?php

namespace Application\Service\Importer;


class Glass extends AbstractImporter
{

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

        foreach ($tables as $table) {

            $this->importOfficialFilters($this->definitions[$table]);

            // Also create a filterSet with same name for the first filter
            $firstFilter = reset($this->cacheFilters);
            $filterSetRepository = $this->getEntityManager()->getRepository('Application\Model\FilterSet');
            $filterSet = $filterSetRepository->getOrCreate($firstFilter->getName());
            foreach ($firstFilter->getChildren() as $child) {
                if ($child->isOfficial()) {
                    $filterSet->addFilter($child);
                }
            }

        }


    }



}