<?php

namespace Application\Repository;

use Doctrine\ORM\Query\Expr\Join;

class UserFilterSetRepository extends AbstractChildRepository
{

    public function getAllWithPermission($action = 'read', $search = null, $parentName = null, \Application\Model\AbstractModel $parent = null)
    {
        $qb = $this->createQueryBuilder('ufs');
        $qb->join('ufs.filterSet', 'filterSet', Join::WITH);
        $qb->join('ufs.user', 'user', Join::WITH);
        $qb->join('ufs.role', 'role', Join::WITH);

        $qb->where('ufs.' . $parentName . ' = :parent');
        $qb->setParameter('parent', $parent);

        $this->addSearch($qb, $search);

        return $qb->getQuery()->getResult();
    }

    public function getAll()
    {

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('filter_set_id', 'filter_set_id');
        $rsm->addScalarResult('user_id', 'user_id');
        $rsm->addScalarResult('role_id', 'role_id');

        $n = $this->getEntityManager()->createNativeQuery('SELECT * FROM user_filter_set', $rsm);
        $res = $n->getResult();
    }

}
