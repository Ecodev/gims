<?php

namespace Application\Repository;

class AnswerRepository extends AbstractChildRepository
{

    /**
     * @var array $cache [questionnaireId => [filterId => [partId => value]]]
     */
    private $cache = array();

    /**
     * Returns the percent value of an answer if it exists.
     * Optimized for mass querying wihtin a Geoname based on a cache.
     * @param integer $questionnaireId
     * @param integer $filterId
     * @param integer $partId
     * @return float|null
     */
    public function getValuePercent($questionnaireId, $filterId, $partId)
    {
        // If no cache for questionnaire, fill the cache
        if (!isset($this->cache[$questionnaireId])) {

            // First we found which geoname is used for the given questionnaire
            $geonameId = $this->getEntityManager()->getRepository('Application\Model\Geoname')->getIdByQuestionnaireId($questionnaireId);

            // Then we get all data for the geoname
            $qb = $this->getEntityManager()->createQueryBuilder()->from('Application\Model\Questionnaire', 'questionnaire')
                    ->select('questionnaire.id AS questionnaire_id, answers.valuePercent, part.id AS part_id, filter.id AS filter_id, officialFilter.id AS official_filter_id')
                    ->leftJoin('questionnaire.answers', 'answers')
                    ->leftJoin('answers.question', 'question')
                    ->leftJoin('answers.part', 'part')
                    ->leftJoin('question.filter', 'filter')
                    ->leftJoin('filter.officialFilter', 'officialFilter')
                    ->andWhere('questionnaire.geoname = :geoname')
            ;

            $qb->setParameters(array(
                'geoname' => $geonameId,
            ));

            $res = $qb->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_SCALAR);

            // Ensure that we hit the cache next time, even if we have no results at all
            $this->cache[$questionnaireId] = array();

            // Restructure cache to be [questionnaireId => [filterId => [partId => value]]]
            foreach ($res as $data) {
                if ($data['official_filter_id']) {
                    $this->cache[$data['questionnaire_id']][$data['official_filter_id']][$data['part_id']] = (float) $data['valuePercent'];
                } else {
                    $this->cache[$data['questionnaire_id']][$data['filter_id']][$data['part_id']] = (float) $data['valuePercent'];
                }
            }
        }

        if (isset($this->cache[$questionnaireId][$filterId][$partId])) {
            return $this->cache[$questionnaireId][$filterId][$partId];
        } else {
            return null;
        }
    }

    /**
     * Returns all items with read access
     * @return array
     */
    public function getAllWithPermission($action = 'read', $parentName = null, \Application\Model\AbstractModel $parent = null)
    {
        $qb = $this->createQueryBuilder('answer')
                ->join('answer.questionnaire', 'questionnaire', \Doctrine\ORM\Query\Expr\Join::WITH)
                ->where($parentName . ' = :parent')
                ->setParameter('parent', $parent)
        ;

        $this->addPermission($qb, 'questionnaire', \Application\Model\Permission::getPermissionName($this, $action));

        return $qb->getQuery()->getResult();
    }

    /**
     * Compute absolute value from percentage value, based on population (for JMP)
     *
     * @param \Application\Model\Answer $answer optional answer to limit on what we compute thing
     * @return integer row modifed count
     */
    public function updateAbsoluteValueFromPercentageValue(\Application\Model\Answer $answer = NULL)
    {
        // if we have an answer we could limit the scope of the request
        $clause = $answer ? 'answer.id = ' . $answer->getId() : 'answer.value_absolute IS NULL';
        $sql = sprintf(
                'UPDATE answer SET value_absolute = p.population * value_percent
                FROM questionnaire q
                    JOIN survey s ON (q.survey_id = s.id)
                    JOIN geoname g ON (q.geoname_id = g.id)
                    JOIN country c ON (c.geoname_id = g.id)
                    JOIN population p ON (p.country_id = c.id AND s.year = p.year)
                    JOIN question ON (s.id = question.survey_id)
                WHERE %s
                    AND answer.part_id = p.part_id
                    AND answer.questionnaire_id = q.id
                    AND answer.question_id = question.id
                    AND question.is_population = true', $clause
        );

        return $this->getEntityManager()->getConnection()->executeUpdate($sql);
    }

    /**
     * Compute absolute value from percentage value, based on population (for JMP)
     *
     * @param \Application\Model\Answer $answer optional answer to limit on what we compute thing
     * @return integer row modifed count
     */
    public function updatePercentValueFromChoiceValue(\Application\Model\Answer $answer)
    {
        // if we have an answer we could limit the scope of the request
        $sql = 'UPDATE answer SET value_percent = ch.value
                FROM choice ch
                WHERE answer.value_choice_id = ch.id and
                answer.id = ' . $answer->getId();

        $this->updateAbsoluteValueFromPercentageValue($answer);

        return $this->getEntityManager()->getConnection()->executeUpdate($sql);
    }

}
