<?php

namespace Application\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Application\Model\SurveyType;
use Application\Model\Rule\Rule;
use Application\Model\Rule\FilterQuestionnaireUsage;

class QuestionnaireRepository extends AbstractChildRepository
{

    private $questionnaireForComputingCache = [];

    /**
     * Returns all items with matching search criteria
     * @param string $action
     * @param string $search
     * @param string $parentName
     * @param \Application\Model\AbstractModel $parent
     * @param \Application\Model\SurveyType $surveyType optionnal restriction on survey type
     * @return array
     */
    public function getAllWithPermission($action = 'read', $search = null, $parentName = null, \Application\Model\AbstractModel $parent = null, SurveyType $surveyType = null)
    {
        $qb = $this->createQueryBuilder('questionnaire');
        $qb->join('questionnaire.survey', 'survey', Join::WITH);
        $qb->join('questionnaire.geoname', 'geoname', Join::WITH);
        $qb->addOrderBy('survey.code');
        $qb->addOrderBy('geoname.name');

        if ($parent) {
            $qb->where($parentName . ' = :parent');
            $qb->setParameter('parent', $parent);
        }

        if ($surveyType) {
            $qb->andWhere('survey.type = :surveyType');
            $qb->setParameter('surveyType', $surveyType);
        }

        if ($action == 'read') {
            $exceptionDql = "questionnaire.status = 'published'";
        } else {
            $exceptionDql = null;
        }

        $this->addPermission($qb, 'questionnaire', \Application\Model\Permission::getPermissionName($this, $action), $exceptionDql);
        $this->addSearch($qb, $search, array('survey.code', 'geoname.name'));

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns all questionnaires for the given geoname (and load their surveys)
     * @param \Application\Model\Geoname|integer $geonameId
     * @return Questionnaires[]
     */
    public function getAllForComputing($geonameId)
    {
        if ($geonameId instanceof \Application\Model\Geoname) {
            $geonameId = $geonameId->getId();
        }

        if (!isset($this->questionnaireForComputingCache[$geonameId])) {

            $questionnairesWithReadAccess = $this->getAllWithPermission();
            $qb = $this->createQueryBuilder('questionnaire');
            $qb->select('questionnaire, survey')
                    ->join('questionnaire.survey', 'survey')
                    ->where('questionnaire.geoname = :geoname')
                    ->andWhere('questionnaire IN (:questionnairesWithReadAccess)')
                    ->orderBy('questionnaire.id');

            $qb->setParameter('geoname', $geonameId);
            $qb->setParameter('questionnairesWithReadAccess', $questionnairesWithReadAccess);
            $questionnaires = $qb->getQuery()->getResult();

            $this->questionnaireForComputingCache[$geonameId] = $questionnaires;
        }

        return $this->questionnaireForComputingCache[$geonameId];
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
