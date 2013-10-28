<?php

namespace Application\Repository\Rule;

class QuestionnaireUsageRepository extends \Application\Repository\AbstractRepository
{

    /**
     * Get all QuestionnaireUsage with any given names within the given questionnaires
     * @param array $ruleNames
     * @param array $questionnaires
     * @return QuestionnaireUsage[]
     */
    public function getAllByRuleName(array $ruleNames, array $questionnaires)
    {
        $qb = $this->createQueryBuilder('qf');
        $qb->join('qf.rule', 'rule', \Doctrine\ORM\Query\Expr\Join::WITH)
                ->andWhere('qf.questionnaire IN (:questionnaires)')
        ;

        $params = array(
            'questionnaires' => $questionnaires,
        );
        $qb->setParameters($params);

        $where = array();
        foreach ($ruleNames as $i => $word) {
            $parameterName = 'word' . $i;
            $where[] = 'LOWER(rule.name) LIKE LOWER(:' . $parameterName . ')';
            $qb->setParameter($parameterName, '%' . $word . '%');
        }
        $qb->andWhere(join(' OR ', $where));

        $questionnaireUsage = $qb->getQuery()->getResult();

        return $questionnaireUsage;
    }

    /**
     * Returns a QuestionnaireUsage for the given triplet
     * @param integer $questionnaireId
     * @param integer $partId
     * @param integer $ruleId
     * @return \Application\Model\Rule\QuestionnaireUsage|null
     */
    public function getOneByQuestionnaire($questionnaireId, $partId, $ruleId)
    {
        // If no cache for questionnaire, fill the cache
        if (!isset($this->cache[$questionnaireId])) {
            $qb = $this->createQueryBuilder('questionnaireUsage')
                    ->select('questionnaireUsage, questionnaire, rule')
                    ->join('questionnaireUsage.questionnaire', 'questionnaire')
                    ->join('questionnaireUsage.rule', 'rule')
                    ->andWhere('questionnaireUsage.questionnaire = :questionnaire')
            ;

            $qb->setParameters(array(
                'questionnaire' => $questionnaireId,
            ));

            $res = $qb->getQuery()->getResult();

            // Restructure cache to be [questionnaireId => [ruleId => [partId => value]]]
            foreach ($res as $questionnaireUsage) {
                $this->cache[$questionnaireUsage->getQuestionnaire()->getId()][$questionnaireUsage->getRule()->getId()][$questionnaireUsage->getPart()->getId()] = $questionnaireUsage;
            }
        }

        if (isset($this->cache[$questionnaireId][$ruleId][$partId]))
            return $this->cache[$questionnaireId][$ruleId][$partId];
        else
            return null;
    }

}
