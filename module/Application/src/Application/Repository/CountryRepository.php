<?php

namespace Application\Repository;

class CountryRepository extends AbstractRepository
{

    public function getAllWithPermission($action = 'read', $search = null)
    {
        $qb = $this->createQueryBuilder('country')
                ->orderBy('country.name');

        $this->addSearch($qb, $search);

        return $qb->getQuery()->getResult();
    }

}
