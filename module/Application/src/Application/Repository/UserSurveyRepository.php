<?php

namespace Application\Repository;

use Doctrine\ORM\Query\Expr\Join;

class UserSurveyRepository extends AbstractChildRepository
{

    public function getAllWithPermission($action = 'read', $search = null, $parentName = null, \Application\Model\AbstractModel $parent = null)
    {
        $qb = $this->createQueryBuilder('us');
        $qb->join('us.survey', 'survey', Join::WITH);
        $qb->join('us.user', 'user', Join::WITH);
        $qb->join('us.role', 'role', Join::WITH);

        $qb->where('us.' . $parentName . ' = :parent');
        $qb->setParameter('parent', $parent);

        $this->addSearch($qb, $search, array('survey.code', 'survey.name', 'user.name', 'role.name'));

        return $qb->getQuery()->getResult();
    }

}
