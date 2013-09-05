<?php

namespace Application\Repository;

class RoleRepository extends AbstractRepository
{

    public function findAll()
    {

        $query = $this->getEntityManager()->createQuery("SELECT r
            FROM Application\Model\Role r
            WHERE
            r.name NOT IN ('anonymous', 'member')
            ORDER BY r.name ASC"
        );

        return $query->getResult();
    }

}
