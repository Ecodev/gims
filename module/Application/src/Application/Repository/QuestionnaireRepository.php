<?php

namespace Application\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Application\Model\Rule\Rule;

class QuestionnaireRepository extends AbstractChildRepository
{

    private $questionnaireForComputingCache = [];

    /**
     * Returns all items with matching search criteria
     * @param string $action
     * @param string $search
     * @param string $parentName
     * @param \Application\Model\AbstractModel $parent
     * @param array $surveyTypes optionnal restriction on survey types
     * @return \Application\Model\Questionnaire[]
     */
    public function getAllWithPermission($action = 'read', $search = null, $parentName = null, \Application\Model\AbstractModel $parent = null, array $surveyTypes = [])
    {
        $queryBuilder = $this->getAllWithPermissionQueryBuilder($action, $search, $parentName, $parent, $surveyTypes);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Returns all questionnaire ID on which we have a permission to read
     * @return integer[]
     */
    private function getAllIdsWithPermission()
    {
        $queryBuilder = $this->getAllWithPermissionQueryBuilder();

        $queryBuilder->select('questionnaire.id');
        $ids = [];
        $result = $queryBuilder->getQuery()->getScalarResult(\Doctrine\ORM\AbstractQuery::HYDRATE_SCALAR);
        foreach ($result as $questionnaire) {
            $ids[] = $questionnaire['id'];
        }

        return $ids;
    }

    /**
     * Returns all items with matching search criteria
     * @param string $action
     * @param string $search
     * @param string $parentName
     * @param \Application\Model\AbstractModel $parent
     * @param array $surveyTypes optionnal restriction on survey types
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getAllWithPermissionQueryBuilder($action = 'read', $search = null, $parentName = null, \Application\Model\AbstractModel $parent = null, array $surveyTypes = [])
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

        if ($surveyTypes) {
            $qb->andWhere('survey.type IN (:surveyTypes)');
            $qb->setParameter('surveyTypes', $surveyTypes);
        }

        if ($action == 'read' || $action == 'update') {
            $exceptionDql = "questionnaire.status = 'published'";
        } else {
            $exceptionDql = null;
        }

        $this->addPermission($qb, ['survey', 'questionnaire'], \Application\Model\Permission::getPermissionName($this, $action), $exceptionDql);
        $this->addSearch($qb, $search, array('survey.code', 'geoname.name'));

        return $qb;
    }

    /**
     * Returns all questionnaires for the given geonames (and load their surveys)
     * @param \Application\Model\Geoname[] $geonames
     * @return Questionnaires[]
     */
    public function getAllForComputing(array $geonames)
    {
        $allInCache = true;
        foreach ($geonames as $geoname) {
            if (!isset($this->questionnaireForComputingCache[$geoname->getId()])) {
                $allInCache = false;
                break;
            }
        }

        if (!$allInCache) {

            $questionnairesWithReadAccess = $this->getAllIdsWithPermission();
            $qb = $this->createQueryBuilder('questionnaire');
            $qb->select('questionnaire, survey')
                    ->join('questionnaire.survey', 'survey')
                    ->where('questionnaire.geoname IN (:geonames)')
                    ->andWhere('questionnaire IN (:questionnairesWithReadAccess)')
                    ->andWhere('questionnaire.status != :rejected')
                    ->orderBy('questionnaire.id');

            $qb->setParameter('geonames', $geonames);
            $qb->setParameter('questionnairesWithReadAccess', $questionnairesWithReadAccess);
            $qb->setParameter('rejected', \Application\Model\QuestionnaireStatus::$REJECTED);
            $questionnaires = $qb->getQuery()->getResult();

            foreach ($geonames as $geoname) {
                $this->questionnaireForComputingCache[$geoname->getId()] = [];
            }

            foreach ($questionnaires as $questionnaire) {
                $this->questionnaireForComputingCache[$questionnaire->getGeoname()->getId()][] = $questionnaire;
            }
        }

        $result = [];
        foreach ($geonames as $geoname) {
            $result = array_merge($result, $this->questionnaireForComputingCache[$geoname->getId()]);
        }

        return $result;
    }

    /**
     * Returns all questionnaires using the given rule
     * @param \Application\Model\Rule\Rule $rule
     * @return \Application\Model\Questionnaire[]
     */
    public function getAllFromRule(Rule $rule)
    {
        // First get all questionnaire ID via fast UNION query
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('questionnaire_id', 'id');
        $qb = $this->getEntityManager()->createNativeQuery('
                SELECT questionnaire_id FROM filter_questionnaire_usage WHERE rule_id = :rule
                UNION
                SELECT questionnaire_id FROM questionnaire_usage WHERE rule_id = :rule
                UNION
                SELECT questionnaire.id AS questionnaire_id FROM questionnaire
                INNER JOIN filter_geoname_usage ON (filter_geoname_usage.geoname_id = questionnaire.geoname_id AND rule_id = :rule)
            ', $rsm);

        $qb->setParameter('rule', $rule->getId());
        $result = $qb->getResult();
        $ids = [];
        foreach ($result as $item) {
            $ids[] = $item['id'];
        }

        // Then load actual objects via standard Doctrine to be sure they are "completely" loaded
        $questionnaires = $this->findById($ids);

        return $questionnaires;
    }

    /**
     * Returns an array of questionnaire with all data carefully handcrafted for /browse/table/filter
     * @param array $ids
     * @param boolean $includePopulation
     * @return array
     */
    public function getCompleteStructure(array $ids, $includePopulation)
    {
        $allowedIds = $this->getAllIdsWithPermission();
        $questionnaireIds = array_intersect($allowedIds, $ids);

        // Fetch data from DB separately to avoid gigantic JOINs
        $questionnaires = $this->getQuestionnaires($questionnaireIds);
        $answers = $this->getAnswers($questionnaireIds);
        $usages = $this->getUsages($questionnaireIds);
        if ($includePopulation) {
            $populations = $this->getPopulations($questionnaireIds);
        }

        // Combine all data together
        foreach ($questionnaires as &$questionnaire) {
            $questionnaire['survey']['year'] = (int) $questionnaire['survey']['year'];
            $questionnaire['name'] = $questionnaire['survey']['code'] . ' - ' . $questionnaire['geoname']['name'];

            // Inject answers into their questions
            foreach ($questionnaire['survey']['questions'] as &$question) {
                $id = $question['id'];
                foreach ($answers as $i => $answer) {
                    if ($answer['question_id'] == $id) {
                        unset($answer['question_id']);
                        unset($answers[$i]);
                        $question['answers'][] = $answer;
                    }
                }
            }

            // Inject populations into their questionnaires
            if ($includePopulation) {
                $questionnaire['populations'] = [];
                $id = $questionnaire['id'];
                foreach ($populations as $i => $population) {
                    if ($population['questionnaire_id'] == $id) {
                        unset($population['questionnaire_id']);
                        unset($populations[$i]);
                        $questionnaire['populations'][] = $population;
                    }
                }
            }

            // Inject usages into their questionnaires
            $questionnaire['hasFilterQuestionnaireUsages'] = $usages[$questionnaire['id']];
        }

        return $questionnaires;
    }

    /**
     * Get questionnaires, geonames, surveys, questions and filters as a hierarchical array
     * @param array $questionnaireIds
     * @return array
     */
    private function getQuestionnaires(array $questionnaireIds)
    {
        $rsm = new \Doctrine\ORM\Query\ResultSetMappingBuilder($this->getEntityManager());
        $rsm->addEntityResult('Application\Model\Questionnaire', 'questionnaire');
        $rsm->addFieldResult('questionnaire', 'questionnaire_id', 'id');
        $rsm->addFieldResult('questionnaire', 'questionnaire_status', 'status');
        $rsm->addFieldResult('questionnaire', 'questionnaire_comments', 'comments');
        $rsm->addJoinedEntityResult('Application\Model\Geoname', 'geoname', 'questionnaire', 'geoname');
        $rsm->addFieldResult('geoname', 'geoname_id', 'id');
        $rsm->addFieldResult('geoname', 'geoname_name', 'name');
        $rsm->addJoinedEntityResult('Application\Model\Survey', 'survey', 'questionnaire', 'survey');
        $rsm->addFieldResult('survey', 'survey_id', 'id');
        $rsm->addFieldResult('survey', 'survey_code', 'code');
        $rsm->addFieldResult('survey', 'survey_year', 'year');
        $rsm->addJoinedEntityResult('Application\Model\Question\NumericQuestion', 'question', 'survey', 'questions');
        $rsm->addFieldResult('question', 'question_id', 'id');
        $rsm->addFieldResult('question', 'question_alternate_names', 'alternateNames');
        $rsm->addFieldResult('question', 'question_is_absolute', 'isAbsolute');
        $rsm->addJoinedEntityResult('Application\Model\Filter', 'question_filter', 'question', 'filter');
        $rsm->addFieldResult('question_filter', 'question_filter_id', 'id');

        $qb1 = $this->getEntityManager()->createNativeQuery('SELECT ' . $rsm->generateSelectClause() . '
            FROM questionnaire
            JOIN geoname ON (geoname.id = questionnaire.geoname_id)
            JOIN survey ON (survey.id = questionnaire.survey_id)
            LEFT JOIN question ON (question.survey_id = survey.id)
            LEFT JOIN filter AS question_filter ON (question_filter.id = question.filter_id)

            WHERE questionnaire.id IN (:questionnaires)
            ', $rsm);

        $qb1->setParameters(array('questionnaires' => $questionnaireIds));
        $res = $qb1->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        // Convert Enum into string
        foreach ($res as &$questionnaire) {
            $questionnaire['status'] = (string) $questionnaire['status'];
        }

        return $res;
    }

    /**
     * Get all answers for questionnaires as a flat array
     * @param array $questionnaireIds
     * @return array
     */
    private function getAnswers(array $questionnaireIds)
    {
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('value_percent', 'valuePercent', 'float');
        $rsm->addScalarResult('value_absolute', 'valueAbsolute', 'float');
        $rsm->addScalarResult('quality', 'quality', 'float');
        $rsm->addScalarResult('questionnaire_id', 'questionnaire_id');
        $rsm->addScalarResult('question_id', 'question_id');
        $rsm->addScalarResult('part_id', 'part_id');

        $qb = $this->getEntityManager()->createNativeQuery('
            SELECT id, value_percent, value_absolute, quality, questionnaire_id, question_id, part_id
            FROM answer
            WHERE answer.questionnaire_id IN (:questionnaires)
            ', $rsm);

        $qb->setParameters(array('questionnaires' => $questionnaireIds));
        $answers = $qb->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        foreach ($answers as &$answer) {
            $this->replaceIdWithObject($answer, 'questionnaire_id');
            $this->replaceIdWithObject($answer, 'part_id');
        }

        return $answers;
    }

    /**
     * Get all FilterQuestionnaireUsages for questionnaires as a flat array
     * @param array $questionnaireIds
     * @return boolean
     */
    private function getUsages(array $questionnaireIds)
    {
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('questionnaire_id', 'questionnaire_id');
        $rsm->addScalarResult('filter_id', 'filter_id');
        $rsm->addScalarResult('part_id', 'part_id');
        $rsm->addScalarResult('is_second_step', 'is_second_step');
        $rsm->addScalarResult('count', 'count');

        $qb = $this->getEntityManager()->createNativeQuery('
            SELECT questionnaire_id, filter_id, part_id, is_second_step, COUNT(*) AS count
            FROM filter_questionnaire_usage
            WHERE questionnaire_id IN (:questionnaires)
            GROUP BY questionnaire_id, filter_id, part_id, is_second_step
        ', $rsm);

        $qb->setParameters(array('questionnaires' => $questionnaireIds));
        $res = $qb->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $usages = [];
        foreach ($res as $usage) {
            $step = $usage['is_second_step'] ? 'second' : 'first';
            $usages[$usage['questionnaire_id']][$usage['filter_id']][$usage['part_id']][$step] = true;
        }

        return $usages;
    }

    /**
     * Get all custom population for questionnaires as a flat array
     * @param array $questionnaireIds
     * @return array
     */
    private function getPopulations(array $questionnaireIds)
    {
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('year', 'year', 'integer');
        $rsm->addScalarResult('population', 'population', 'integer');
        $rsm->addScalarResult('part_id', 'part_id');
        $rsm->addScalarResult('questionnaire_id', 'questionnaire_id');

        $qb = $this->getEntityManager()->createNativeQuery('
            SELECT id, year, population, part_id, questionnaire_id
            FROM population
            WHERE questionnaire_id IN (:questionnaires)
        ', $rsm);

        $qb->setParameters(array('questionnaires' => $questionnaireIds));
        $populations = $qb->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        foreach ($populations as &$population) {
            $this->replaceIdWithObject($population, 'part_id');
        }

        return $populations;
    }

    /**
     * Replace an ID stored in ['part_id' => 123], with a sub-array ['part' => ['id' => 123]]
     * @param array $object
     * @param string $property
     */
    private function replaceIdWithObject(array &$object, $property)
    {
        $newProperty = str_replace('_id', '', $property);
        if ($object[$property]) {
            $object[$newProperty] = [
                'id' => $object[$property]
            ];
        } else {
            $object[$newProperty] = null;
        }
        unset($object[$property]);
    }

}
