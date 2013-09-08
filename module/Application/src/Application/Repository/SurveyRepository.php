<?php

namespace Application\Repository;

class SurveyRepository extends AbstractRepository
{

    /**
     * Returns all items with read access
     * @return array
     */
    public function getAllWithPermission($parentName, \Application\Model\AbstractModel $parent = null)
    {
        $permissionDql = $this->getPermissionDql('survey', 'Survey-read');
        $query = $this->getEntityManager()->createQuery("SELECT survey
            FROM Application\Model\Survey survey
            $permissionDql
            ORDER BY survey.year DESC, survey.name ASC"
        );

        return $query->getResult();
    }

}
