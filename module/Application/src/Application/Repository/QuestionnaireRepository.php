<?php

namespace Application\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Application\Model\Rule\Rule;
use Application\Model\Rule\FilterQuestionnaireUsage;

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

        if ($action == 'read') {
            $exceptionDql = "questionnaire.status = 'published'";
        } else {
            $exceptionDql = null;
        }

        $this->addPermission($qb, 'questionnaire', \Application\Model\Permission::getPermissionName($this, $action), $exceptionDql);

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

    /**
     * Duplicate all usages related to source questionnaire and replacing formula with destination questionnaire
     * @param \Application\Model\Questionnaire $destQ
     * @param \Application\Model\Questionnaire $srcQ
     */
    public function copyFilterUsages(\Application\Model\Questionnaire $destQ, \Application\Model\Questionnaire $srcQ)
    {

        $fqus = $srcQ->getFilterQuestionnaireUsages();

        foreach ($fqus as $fqu) {

            // replace questionnaire id in formula
            $formula = $fqu->getRule()->getFormula();
            $newFormula = str_replace('Q#' . $srcQ->getId(), 'Q#' . $destQ->getId(), $formula);

            $newRule = new Rule($fqu->getRule()->getName());
            $newRule->setFormula($newFormula);

            $newFqu = new FilterQuestionnaireUsage();
            $newFqu->setFilter($fqu->getFilter());
            $newFqu->setQuestionnaire($destQ);
            $newFqu->setPart($fqu->getPart());
            $newFqu->setRule($newRule);
            $newFqu->setJustification($fqu->getJustification());

            $this->getEntityManager()->persist($newRule);
            $this->getEntityManager()->persist($newFqu);
        }

        $this->getEntityManager()->flush();
    }

}
