<?php

namespace Application\Repository;

use Application\Model\Filter;

class FilterRepository extends AbstractRepository
{

    public function getOrCreate($name)
    {
        $filter = $this->findOneByName($name);
        if (!$filter) {
            $filter = new Filter($name);
            $this->getEntityManager()->persist($filter);
        }

        return $filter;
    }

}
