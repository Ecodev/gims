<?php

namespace Application\Repository\Rule;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;

class FilterQuestionnaireUsageRepository extends \Application\Repository\AbstractRepository
{

    /**
     * {@inheritdoc}
     * @param string $action
     * @param string $search
     * @param string $parentName
     * @param \Application\Model\AbstractModel $parent
     * @return FilterQuestionnaireUsage[]
     */
    public function getAllWithPermission($action = 'read', $search = null, $parentName = null, \Application\Model\AbstractModel $parent = null)
    {
        $qb = $this->createQueryBuilder('fqu');
        $qb->join('fqu.rule', 'rule', Join::WITH);
        $qb->join('fqu.filter', 'filter', Join::WITH);
        $qb->join('fqu.questionnaire', 'questionnaire', Join::WITH);
        $qb->join('fqu.part', 'part', Join::WITH);
        $qb->join('questionnaire.survey', 'survey');

        $qb->where('fqu.' . $parentName . ' = :parent');
        $qb->setParameter('parent', $parent);

        $this->addSearch($qb, $search, array('filter.name', 'rule.name', 'rule.formula', 'survey.code', 'survey.name', 'part.name'));

        return $qb->getQuery()->getResult();
    }

    /**
     * @var array $cache [questionnaireId => [filterId => [partId => value]]]
     */
    private $cache = array();

    /**
     * Return the first FilterQuestionUsage
     * @param integer $questionnaireId
     * @param integer $filterId
     * @param integer $partId
     * @param boolean $useSecondStepRules if true returns only second step usages, if false, returns only first step usages
     * @param \Doctrine\Common\Collections\ArrayCollection $excluded
     * @return FilterQuestionnaireUsage|null
     */
    public function getFirst($questionnaireId, $filterId, $partId, $useSecondStepRules, ArrayCollection $excluded)
    {
        $possible = $this->getAll($questionnaireId, $filterId, $partId);

        // Returns the first non-excluded and according to its step
        foreach ($possible as $filterQuestionnaireUsage) {
            if ($useSecondStepRules == $filterQuestionnaireUsage->isSecondStep() && !$excluded->contains($filterQuestionnaireUsage)) {
                return $filterQuestionnaireUsage;
            }
        }

        return null;
    }

    /**
     * Return all FilterQuestionUsage for the given questionnaire, filter and part
     * @param integer $questionnaireId
     * @param integer $filterId
     * @param integer $partId
     * @return FilterQuestionnaireUsage[]
     */
    public function getAll($questionnaireId, $filterId, $partId)
    {
        $this->fillCache($questionnaireId);

        if (isset($this->cache[$questionnaireId][$filterId][$partId])) {
            return $this->cache[$questionnaireId][$filterId][$partId];
        } else {
            return [];
        }
    }

    /**
     * If no cache for questionnaire, fill the cache
     * @param integer $questionnaireId
     */
    private function fillCache($questionnaireId)
    {
        if (!isset($this->cache[$questionnaireId])) {

            // First we found which geoname is used for the given questionnaire
            $geonameId = $this->getEntityManager()->getRepository('Application\Model\Geoname')->getIdByQuestionnaireId($questionnaireId);

            // Then we get all data for the geoname
            $qb = $this->createQueryBuilder('filterQuestionnaireUsage')
                    ->select('filterQuestionnaireUsage, questionnaire, filter, rule')
                    ->join('filterQuestionnaireUsage.questionnaire', 'questionnaire')
                    ->join('filterQuestionnaireUsage.filter', 'filter')
                    ->join('filterQuestionnaireUsage.rule', 'rule')
                    ->andWhere('questionnaire.geoname = :geoname')
                    ->orderBy('filterQuestionnaireUsage.isSecondStep DESC, filterQuestionnaireUsage.sorting, filterQuestionnaireUsage.id')
            ;

            $qb->setParameters(array(
                'geoname' => $geonameId,
            ));

            $res = $qb->getQuery()->getResult();

            // Ensure that we hit the cache next time, even if we have no results at all
            $this->cache[$questionnaireId] = array();

            // Restructure cache to be [questionnaireId => [filterId => [partId => value]]]
            foreach ($res as $filterQuestionnaireUsage) {
                $this->cache[$filterQuestionnaireUsage->getQuestionnaire()->getId()][$filterQuestionnaireUsage->getFilter()->getId()][$filterQuestionnaireUsage->getPart()->getId()][] = $filterQuestionnaireUsage;
            }
        }
    }

}
