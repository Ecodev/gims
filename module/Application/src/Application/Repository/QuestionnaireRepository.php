<?php

namespace Application\Repository;

use Doctrine\ORM\Query\Expr\Join;

class QuestionnaireRepository extends AbstractChildRepository
{

    /**
     * Returns all items with matching search criteria
     * @return array
     */
    public function getAllWithPermission($action = 'read', $search = null, $parentName = null, \Application\Model\AbstractModel $parent = null)
    {

        $qb = $this->createQueryBuilder('questionnaire');
        $qb->join('questionnaire.survey', 'survey', \Doctrine\ORM\Query\Expr\Join::WITH);

        if ($parent) {
            $qb->where($parentName . ' = :parent');
            $qb->setParameter('parent', $parent);
        }

        $this->addPermission($qb, 'questionnaire', \Application\Model\Permission::getPermissionName($this, $action));

        if ($search) {
            $qb->join('questionnaire.geoname', 'g', \Doctrine\ORM\Query\Expr\Join::WITH);
            $this->addSearch($qb, $search, array('survey.code', 'g.name'));
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns all questionnaires for th given geoname (and load their surveys)
     * @param \Application\Model\Geoname|integer $geoname
     * @return Questionnaires[]
     */
    public function getByGeonameWithSurvey($geoname)
    {
        $qb = $this->createQueryBuilder('questionnaire');
        $qb->select('questionnaire, survey')
                ->join('questionnaire.survey', 'survey')
                ->where('questionnaire.geoname = :geoname')
                ->orderBy('questionnaire.id');

        $qb->setParameter('geoname', $geoname);

        return $qb->getQuery()->getResult();
    }

}
