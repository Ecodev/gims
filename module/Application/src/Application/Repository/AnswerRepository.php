<?php

namespace Application\Repository;

class AnswerRepository extends AbstractChildRepository
{

    /**
     * @var array $cache [questionnaireId => [filterId => [partId => ['value' => value, 'questionName' => question]]]]
     */
    private $cache = [];

    /**
     * Fill the cache for answer's valuePercent and questionName
     * @param integer $questionnaireId
     */
    private function fillCache($questionnaireId)
    {
        // If no cache for questionnaire, fill the cache
        if (!isset($this->cache[$questionnaireId])) {

            // First we found which geoname is used for the given questionnaire
            $geonameId = $this->getEntityManager()
                    ->getRepository(\Application\Model\Geoname::class)
                    ->getIdByQuestionnaireId($questionnaireId);

            // use native query instead of query builder, because answers are related
            // to AbstractAnswerableQuestions and can't access to child class NumericQuestion::is_absolute
            $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
            $rsm->addScalarResult('questionnaire_id', 'questionnaire_id');
            $rsm->addScalarResult('valuePercent', 'valuePercent');
            $rsm->addScalarResult('valueAbsolute', 'valueAbsolute');
            $rsm->addScalarResult('quality', 'quality');
            $rsm->addScalarResult('part_id', 'part_id');
            $rsm->addScalarResult('filter_id', 'filter_id');
            $rsm->addScalarResult('questionName', 'questionName');
            $rsm->addScalarResult('questionIsAbsolute', 'questionIsAbsolute');

            $qb = $this->getEntityManager()->createNativeQuery('
                SELECT
                    q.id AS questionnaire_id,
                    a.value_percent as valuePercent,
                    a.value_absolute as valueAbsolute,
                    a.quality as quality,
                    p.id AS part_id,
                    f.id AS filter_id,
                    qu.name AS questionName,
                    qu.is_absolute as questionIsAbsolute
                FROM questionnaire q
                LEFT JOIN answer AS a ON a.questionnaire_id = q.id
                LEFT JOIN question AS qu ON a.question_id = qu.id
                LEFT JOIN part AS p on a.part_id = p.id
                LEFT JOIN filter AS f on qu.filter_id = f.id
                WHERE q.geoname_id = :geonameId', $rsm);

            $qb->setParameters(['geonameId' => $geonameId]);
            $res = $qb->getResult();

            // Ensure that we hit the cache next time, even if we have no results at all
            $this->cache[$questionnaireId] = [];

            // Restructure cache
            foreach ($res as $data) {
                $valuePercent = is_null($data['valuePercent']) ? null : (float) $data['valuePercent'];
                $valueAbsolute = is_null($data['valueAbsolute']) ? null : (float) $data['valueAbsolute'];
                $value = $data['questionIsAbsolute'] ? $valueAbsolute : $valuePercent;
                $value *= $data['quality'];

                $answerData = [
                    'value' => $value,
                    'questionName' => $data['questionName'],
                ];

                $this->cache[$data['questionnaire_id']][$data['filter_id']][$data['part_id']] = $answerData;
            }
        }
    }

    /**
     * Returns the percent value of an answer if it exists.
     * Optimized for mass querying within a Geoname based on a cache.
     * @param integer $questionnaireId
     * @param integer $filterId
     * @param integer $partId
     * @return float|null
     */
    public function getValue($questionnaireId, $filterId, $partId)
    {
        $this->fillCache($questionnaireId);

        if (isset($this->cache[$questionnaireId][$filterId][$partId])) {
            return $this->cache[$questionnaireId][$filterId][$partId]['value'];
        } else {
            return null;
        }
    }

    /**
     * Returns Question name, but only if a non-null answer exists. Otherwise NULL
     * Optimized for mass querying within a Geoname based on a cache.
     * @param integer $questionnaireId
     * @param integer $filterId
     * @return string|null
     */
    public function getQuestionNameIfNonNullAnswer($questionnaireId, $filterId)
    {
        $this->fillCache($questionnaireId);

        if (isset($this->cache[$questionnaireId][$filterId])) {
            foreach ($this->cache[$questionnaireId][$filterId] as $answerData) {
                if (!is_null($answerData['value'])) {
                    return $answerData['questionName'];
                }
            }
        } else {
            return null;
        }
    }

    /**
     * Returns all items with read access
     * @param string $action
     * @param null $search
     * @param null $parentName
     * @param \Application\Model\AbstractModel $parent
     * @return array
     */
    public function getAllWithPermission($action = 'read', $search = null, $parentName = null, \Application\Model\AbstractModel $parent = null)
    {
        $qb = $this->createQueryBuilder('answer')
                ->join('answer.questionnaire', 'questionnaire', \Doctrine\ORM\Query\Expr\Join::WITH)
                ->where($parentName . ' = :parent')
                ->setParameter('parent', $parent);

        $this->addPermission($qb, 'questionnaire', \Application\Model\Permission::getPermissionName($this, $action));

        return $qb->getQuery()->getResult();
    }

    /**
     * Depending on answer.question.isAbsolute, this method completes valuePercent or valueAbsolute
     * @param \Application\Model\Answer $answer
     * @return int
     */
    public function completePopulationAnswer(\Application\Model\Answer $answer = null)
    {
        $isAbsolute = $answer && $answer->getQuestion() instanceof \Application\Model\Question\NumericQuestion && $answer->getQuestion()->isAbsolute();

        if ($isAbsolute) {
            $computing = "value_percent = value_absolute / p.population";
        } else {
            $computing = "value_absolute = p.population * value_percent";
        }

        if ($answer) {
            $whereClause = 'answer.id = ' . $answer->getId();
        } else {
            $whereClause = 'question.is_absolute = FALSE';
        }

        $sql = sprintf('UPDATE answer
                    JOIN questionnaire q ON (answer.questionnaire_id = q.id)
                    JOIN survey s ON (q.survey_id = s.id)
                    JOIN geoname g ON (q.geoname_id = g.id)
                    JOIN population p ON (p.geoname_id = g.id AND s.year = p.year)
                    JOIN question ON (s.id = question.survey_id)
                SET %s
                WHERE %s
                    AND answer.part_id = p.part_id
                    AND answer.question_id = question.id
                    AND question.is_population = TRUE', $computing, $whereClause);

        return $this->getEntityManager()->getConnection()->executeUpdate($sql);
    }

    /**
     * Compute absolute value from percentage value, based on population (for JMP)
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

        $res = $this->getEntityManager()->getConnection()->executeUpdate($sql);
        $this->completePopulationAnswer($answer);

        return $res;
    }

    public function getAllAnswersInQuestionnaires(\Application\Model\Survey $survey, array $questionnairesIds)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('answer, question, questionnaire, choice, part')
                ->from(\Application\Model\Answer::class, 'answer')
                ->join('answer.questionnaire', 'questionnaire')
                ->join('answer.question', 'question')
                ->join('answer.part', 'part')
                ->leftJoin('answer.valueChoice', 'choice');

        if ($questionnairesIds) {
            $qb->where('questionnaire IN (:questionnaireIds)');
            $qb->setParameter('questionnaireIds', $questionnairesIds);
        } else {
            $qb->join('questionnaire.survey', 'survey')
                    ->where('survey.id = :surveyid')
                    ->setParamter('surveyId', $survey->getId());
        }

        return $qb->getQuery()->getArrayResult();
    }
}
