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

    /**
     * Returns all items with read access
     * @return array
     */
    public function agetAllWithPermission($action = 'read')
    {
        $permissionDql = $this->addPermission('survey', 'Survey-read');
        $query = $this->getEntityManager()->createQuery("SELECT survey
            FROM Application\Model\Survey survey
            $permissionDql
            ORDER BY survey.year DESC, survey.name ASC"
        );

        return $query->getResult();
    }

}
