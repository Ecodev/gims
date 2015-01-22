<?php

namespace Application\Repository;

use Doctrine\ORM\Query\Expr\Join;

class UserQuestionnaireRepository extends AbstractChildRepository
{

    /**
     * {@inheritdoc}
     * @param string $action
     * @param string $search
     * @param string $parentName
     * @param \Application\Model\AbstractModel $parent
     * @return UserQuestionnaire[]
     */
    public function getAllWithPermission($action = 'read', $search = null, $parentName = null, \Application\Model\AbstractModel $parent = null)
    {
        $qb = $this->createQueryBuilder('uq');
        $qb->join('uq.questionnaire', 'questionnaire', Join::WITH);
        $qb->join('uq.user', 'user', Join::WITH);
        $qb->join('uq.role', 'role', Join::WITH);
        $qb->join('questionnaire.survey', 'survey');
        $qb->join('questionnaire.geoname', 'geoname');

        $qb->where('uq.' . $parentName . ' = :parent');
        $qb->setParameter('parent', $parent);

        $this->addSearch($qb, $search, ['survey.code', 'geoname.name', 'user.name', 'role.name']);

        return $qb->getQuery()->getResult();
    }

}
