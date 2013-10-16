<?php

namespace Application\Repository\Rule;

use Doctrine\Common\Collections\ArrayCollection;

class FilterRuleRepository extends \Application\Repository\AbstractRepository
{

    private $cache = array();
    private $cacheExcluded = null;

    /**
     * Returns the percent value of an answer if it exists.
     * Optimized for mass querying wihtin a Questionnaire based on a cache.
     * @param integer $questionnaireId
     * @param integer $filterId
     * @param integer $partId
     * @param \Doctrine\Common\Collections\ArrayCollection $excluded
     * @return FilterRule|null
     */
    public function getFirstWithFormula($questionnaireId, $filterId, $partId, ArrayCollection $excluded)
    {
        // If no cache for questionnaire, fill the cache
        if (!isset($this->cache[$questionnaireId])) {
            $qb = $this->createQueryBuilder('filterRule')
                    ->select('filterRule, questionnaire, filter, rule')
                    ->join('filterRule.questionnaire', 'questionnaire')
                    ->join('filterRule.filter', 'filter')
                    ->join('filterRule.rule', 'rule')
                    ->andWhere('filterRule.questionnaire = :questionnaire')
            ;

            $qb->setParameters(array(
                'questionnaire' => $questionnaireId,
            ));

            $res = $qb->getQuery()->getResult();

            // Restructure cache to be [questionnaireId => [filterId => [partId => value]]]
            foreach ($res as $filterRule) {
                if ($filterRule->getFormula()) {
                    $this->cache[$filterRule->getQuestionnaire()->getId()][$filterRule->getFilter()->getId()][$filterRule->getPart()->getId()][] = $filterRule;
                }
            }
        }

        if (isset($this->cache[$questionnaireId][$filterId][$partId]))
            $possible = $this->cache[$questionnaireId][$filterId][$partId];
        else
            $possible = array();

        foreach ($possible as $filterRule) {
            if (!$excluded->contains($filterRule))
                return $filterRule;
        }

        return null;
    }

    /**
     * Returns whether the filter in the given questionnaire and part is excluded
     * @param integer $questionnaireId
     * @param integer $filterId
     * @param integer $partId
     * @return boolean
     */
    public function isExcluded($questionnaireId, $filterId, $partId)
    {

        // If no cache for questionnaire, fill the cache
        if (is_null($this->cacheExcluded)) {
            $qb = $this->createQueryBuilder('filterRule')
                    ->select('questionnaire.id AS questionnaire_id, filter.id AS filter_id, part.id AS part_id')
                    ->join('filterRule.questionnaire', 'questionnaire')
                    ->join('filterRule.filter', 'filter')
                    ->join('filterRule.part', 'part')
                    ->join('filterRule.rule', 'rule')
                    ->andWhere('rule INSTANCE OF Application\Model\Rule\Exclude')
            ;


            $res = $qb->getQuery()->getResult();

            // Restructure cache to be [questionnaireId => [filterId => [partId => true]]]
            foreach ($res as $data) {
                $this->cacheExcluded[$data['questionnaire_id']][$data['filter_id']][$data['part_id']] = true;
            }
        }

        return isset($this->cacheExcluded[$questionnaireId][$filterId][$partId]);
    }

}
