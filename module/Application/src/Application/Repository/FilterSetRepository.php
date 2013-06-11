<?php

namespace Application\Repository;

use Application\Model\FilterSet;

class FilterSetRepository extends AbstractRepository
{

    use Traits\OrderedByName;

    /**
     * Returns a FilterSet either from database, or newly created
     * @param string $name
     * @return \Application\Model\FilterSet
     */
    public function getOrCreate($name)
    {
        $filterSet = $this->findOneByName($name);
        if (!$filterSet) {
            $filterSet = new FilterSet($name);
            $this->getEntityManager()->persist($filterSet);
        }

        return $filterSet;
    }

}
