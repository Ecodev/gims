<?php

namespace Application\Repository\Rule;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Model\Questionnaire;
use Application\Model\Part;

class FilterRuleRepository extends \Application\Repository\AbstractRepository
{

    private $cache = array();

    /**
     * Returns the percent value of an answer if it exists.
     * Optimized for mass querying wihtin a Questionnaire based on a cache.
     * @param \Application\Model\Questionnaire $questionnaire
     * @param integer $filterId
     * @param \Application\Model\Part $part
     * @param \Doctrine\Common\Collections\ArrayCollection $excluded
     * @return FilterRule|null
     */
    public function getFirstWithFormula(Questionnaire $questionnaire, $filterId, Part $part, ArrayCollection $excluded)
    {
        // If no cache for questionnaire, fill the cache
        if (!isset($this->cache[$questionnaire->getId()])) {
            $qb = $this->createQueryBuilder('filterRule')
                    ->select('filterRule, questionnaire, filter, rule')
                    ->join('filterRule.questionnaire', 'questionnaire')
                    ->join('filterRule.filter', 'filter')
                    ->join('filterRule.rule', 'rule')
                    ->andWhere('filterRule.questionnaire = :questionnaire')
            ;

            $qb->setParameters(array(
                'questionnaire' => $questionnaire,
            ));

            $res = $qb->getQuery()->getResult();

            // Restructure cache to be [questionnaireId => [filterId => [partId => value]]]
            foreach ($res as $filterRule) {
                if ($filterRule->getFormula()) {
                    $this->cache[$filterRule->getQuestionnaire()->getId()][$filterRule->getFilter()->getId()][$filterRule->getPart()->getId()][] = $filterRule;
                }
            }
        }

        $possible = @$this->cache[$questionnaire->getId()][$filterId][$part->getId()] ? : array();

        foreach ($possible as $filterRule) {
            if (!$excluded->contains($filterRule))
                return $filterRule;
        }

        return null;
    }

}
