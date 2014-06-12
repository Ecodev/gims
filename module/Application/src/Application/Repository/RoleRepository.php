<?php

namespace Application\Repository;

class RoleRepository extends AbstractRepository
{

    /**
     * Returns all roles except built-in roles
     * @param string $action
     * @param string $search
     * @return array
     */
    public function getAllWithPermission($action = 'read', $search = null)
    {
        $qb = $this->createQueryBuilder('role');

        // Never list built-in roles, because they should not be used by end-user to define permissions
        $qb->where('role.name NOT IN(:roles)');
        $qb->setParameter('roles', ['anonymous', 'member']);

        $this->addSearch($qb, $search);

        return $qb->getQuery()->getResult();
    }

}
