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

}
