<?php

namespace Application\Repository;

use Application\Model\FilterSet;

class FilterSetRepository extends AbstractRepository
{

    use Traits\OrderedByName;

    /**
     * Returns all items with permissions
     * @param string $action
     * @return array
     */
    public function getAllWithPermission($action = 'read', $search = null)
    {
        $qb = $this->createQueryBuilder('filterSet');
        $qb->orderBy('filterSet.name', 'ASC');

        $this->addSearch($qb, $search);

        return $qb->getQuery()->getResult();
    }

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
