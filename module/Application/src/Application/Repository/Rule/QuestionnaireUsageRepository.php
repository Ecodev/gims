<?php

namespace Application\Repository\Rule;

class QuestionnaireUsageRepository extends \Application\Repository\AbstractRepository
{

    /**
     * @var array $cache [questionnaireId => [ruleId => [partId => value]]]
     */
    private $cache = array();

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

            // First we found which geoname is used for the given questionnaire
            $geonameId = $this->getEntityManager()->getRepository('Application\Model\Geoname')->getIdByQuestionnaireId($questionnaireId);

            // Then we get all data for the geoname
            $qb = $this->createQueryBuilder('questionnaireUsage')
                    ->select('questionnaireUsage, questionnaire, rule')
                    ->join('questionnaireUsage.questionnaire', 'questionnaire')
                    ->join('questionnaireUsage.rule', 'rule')
                    ->andWhere('questionnaire.geoname = :geoname')
            ;

            $qb->setParameters(array(
                'geoname' => $geonameId,
            ));

            $res = $qb->getQuery()->getResult();

            // Ensure that we hit the cache next time, even if we have no results at all
            $this->cache[$questionnaireId] = array();

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
