<?php

namespace Application\Repository;

class SurveyRepository extends AbstractRepository
{

    /**
     * Returns all items with read access
     * @param string $action
     * @param string $search
     * @param array $surveyTypes optionnal restriction on survey types
     * @return array
     */
    public function getAllWithPermission($action = 'read', $search = null, array $surveyTypes = [])
    {
        $qb = $this->createQueryBuilder('survey')
                ->addOrderBy('survey.year', 'DESC')
                ->addOrderBy('survey.name', 'ASC');

        if ($surveyTypes) {
            $qb->andWhere('survey.type IN (:surveyTypes)');
            $qb->setParameter('surveyTypes', $surveyTypes);
        }

        $this->addSearch($qb, $search);
        $this->addPermission($qb, 'survey', \Application\Model\Permission::getPermissionName($this, $action));

        return $qb->getQuery()->getResult();
    }

}
