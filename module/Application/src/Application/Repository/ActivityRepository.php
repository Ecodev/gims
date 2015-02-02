<?php

namespace Application\Repository;

use Doctrine\ORM\Query\Expr\Join;

class ActivityRepository extends AbstractChildRepository
{

    /**
     * {@inheritdoc}
     * @param string $action
     * @param string $search
     * @param string $parentName
     * @param \Application\Model\AbstractModel $parent
     * @return Activity[]
     */
    public function getAllWithPermission($action = 'read', $search = null, $parentName = null, \Application\Model\AbstractModel $parent = null)
    {
        $qb = $this->createQueryBuilder('activity');
        $qb->join('activity.creator', 'creator', Join::WITH);
        $qb->orderBy('activity.dateCreated', 'desc');

        if ($parentName == 'user') {
            $qb->where('activity.creator = :parent');
            $qb->setParameter('parent', $parent);
        }

        return $qb->getQuery()->getResult();
    }
}
