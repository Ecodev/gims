<?php

namespace Application\Repository\Rule;

use Doctrine\Common\Collections\ArrayCollection;

class FilterQuestionnaireUsageRepository extends \Application\Repository\AbstractRepository
{

    private $cache = array();

    /**
     * Return the first FilterQuestionUsage
     * @param integer $questionnaireId
     * @param integer $filterId
     * @param integer $partId
     * @param \Doctrine\Common\Collections\ArrayCollection $excluded
     * @return FilterQuestionnaireUsage|null
     */
    public function getFirst($questionnaireId, $filterId, $partId, ArrayCollection $excluded)
    {
        // If no cache for questionnaire, fill the cache
        if (!isset($this->cache[$questionnaireId])) {
            $qb = $this->createQueryBuilder('filterQuestionnaireUsage')
                    ->select('filterQuestionnaireUsage, questionnaire, filter, rule')
                    ->join('filterQuestionnaireUsage.questionnaire', 'questionnaire')
                    ->join('filterQuestionnaireUsage.filter', 'filter')
                    ->join('filterQuestionnaireUsage.rule', 'rule')
                    ->andWhere('filterQuestionnaireUsage.questionnaire = :questionnaire')
                    ->orderBy('filterQuestionnaireUsage.sorting, filterQuestionnaireUsage.id')
            ;

            $qb->setParameters(array(
                'questionnaire' => $questionnaireId,
            ));

            $res = $qb->getQuery()->getResult();

            // Restructure cache to be [questionnaireId => [filterId => [partId => value]]]
            foreach ($res as $filterQuestionnaireUsage) {
                $this->cache[$filterQuestionnaireUsage->getQuestionnaire()->getId()][$filterQuestionnaireUsage->getFilter()->getId()][$filterQuestionnaireUsage->getPart()->getId()][] = $filterQuestionnaireUsage;
            }
        }

        if (isset($this->cache[$questionnaireId][$filterId][$partId]))
            $possible = $this->cache[$questionnaireId][$filterId][$partId];
        else
            $possible = array();

        foreach ($possible as $filterQuestionnaireUsage) {
            if (!$excluded->contains($filterQuestionnaireUsage))
                return $filterQuestionnaireUsage;
        }

        return null;
    }

}
