<?php

namespace Application\Repository;

class SurveyRepository extends AbstractRepository
{

    /**
     * Returns all items with read access
     * @return array
     */
    public function getAllWithPermission($action = 'read', $search = null)
    {
        $qb = $this->createQueryBuilder('survey')
                ->addOrderBy('survey.year', 'DESC')
                ->addOrderBy('survey.name', 'ASC')
        ;

        $this->addSearch($qb, $search);
        $this->addPermission($qb, 'survey', \Application\Model\Permission::getPermissionName($this, $action));

        return $qb->getQuery()->getResult();
    }

}
