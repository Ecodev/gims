<?php

namespace Application\Repository;

use Doctrine\ORM\Query\Expr\Join;

class GeonameRepository extends AbstractRepository
{

    private $cache = array();

    public function getAllWithPermission($action = 'read', $search = null)
    {
        $qb = $this->createQueryBuilder('geoname')->orderBy('geoname.name');

        $this->addSearch($qb, $search);

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns the Geoname ID for the given questionnaire (and use cache for subsequent usage)
     * @param integer $questionnaireId
     * @return integer
     */
    public function getIdByQuestionnaireId($questionnaireId)
    {

        if (!isset($this->cache[$questionnaireId])) {

            $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
            $rsm->addScalarResult('questionnaire_id', 'questionnaire_id');
            $rsm->addScalarResult('geoname_id', 'geoname_id');

            $n = $this->getEntityManager()->createNativeQuery('
SELECT questionnaire.id AS questionnaire_id, questionnaire.geoname_id AS geoname_id
FROM questionnaire
INNER JOIN questionnaire AS q ON questionnaire.geoname_id = q.geoname_id AND q.id = :questionnaire', $rsm);

            $n->setParameter('questionnaire', $questionnaireId);
            $res = $n->getResult();

            foreach ($res as $data) {
                $this->cache[$data['questionnaire_id']] = $data['geoname_id'];
            }
        }

        return $this->cache[$questionnaireId];
    }

    /**
     * Return the root NSA filter of asked geoname or null
     * @param $geonameId
     * @return \Application\Model\Filter|null
     */
    public function getNsaFilter($geonameId)
    {
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('name', 'name');

        $n = $this->getEntityManager()->createNativeQuery('
            SELECT grandparent.id as id, grandparent.name as name
            FROM question q
            LEFT JOIN survey AS s ON q.survey_id = s.id
            LEFT JOIN questionnaire AS qu ON qu.survey_id = s.id
            LEFT JOIN filter AS f ON q.filter_id = f.id
            LEFT JOIN filter_children AS parentrel on parentrel.child_filter_id = f.id
            LEFT JOIN filter_children AS grandparentrel on grandparentrel.child_filter_id = parentrel.filter_id
            LEFT JOIN filter AS grandparent on grandparent.id = grandparentrel.filter_id
            WHERE s.type = :type
            AND qu.geoname_id = :geoname
        ', $rsm);

        $n->setParameter('geoname', $geonameId);
        $n->setParameter('type', \Application\Model\SurveyType::$NSA);
        $filter = $n->getResult();

        $result = count($filter) ? $filter[0] : null;

        return $result;
    }

    /**
     * Returns geonames with their children already loaded
     * @param array $geonameIds
     * @return \Application\Model\Geoname[]
     */
    public function getByIdForComputing(array $geonameIds)
    {
        $qb = $this->createQueryBuilder('geoname')
                ->select('geoname', 'children', 'grandChildren')
                ->leftJoin('geoname.children', 'children', Join::WITH)
                ->leftJoin('children.children', 'grandChildren', Join::WITH)
                ->where('geoname IN (:geonames)')
                ->orderBy('geoname.name');

        $qb->setParameter('geonames', $geonameIds);

        return $qb->getQuery()->getResult();
    }

    /**
     * Compute population for all geonames with children
     */
    public function computeAllPopulation()
    {
        $populationRepository = $this->getEntityManager()->getRepository('Application\Model\Population');
        $parts = $this->getEntityManager()->getRepository('Application\Model\Part')->findAll();
        $calculator = new \Application\Service\Calculator\Calculator();
        $years = $calculator->getYears();

        $qb = $this->createQueryBuilder('geoname')->where('SIZE(geoname.children) > 0');
        $geonamesWithChildren = $qb->getQuery()->getResult();

        foreach ($geonamesWithChildren as $geoname) {
            foreach ($parts as $part) {
                foreach ($years as $year) {
                    $population = $this->computePopulation($geoname, $part, $year);
                    $populationRepository->updateOrCreate($geoname, $part, $year, $population);
                }
            }
        }
        $this->getEntityManager()->flush();
    }

    /**
     * Returns the computed population
     * @param \Application\Model\Geoname $geoname
     * @param \Application\Model\Part $part
     * @param integer $year
     * @return integer
     */
    private function computePopulation(\Application\Model\Geoname $geoname, \Application\Model\Part $part, $year)
    {
        $populationRepository = $this->getEntityManager()->getRepository('Application\Model\Population');

        $children = $geoname->getChildren();
        if (count($children)) {
            $populationTotal = null;
            foreach ($children as $child) {
                $populationTotal += $this->computePopulation($child, $part, $year);
            }

            return $populationTotal;
        } else {
            $population = $populationRepository->getPopulationByGeoname($geoname, $part->getId(), $year);

            return $population;
        }
    }

    public function getAllWithJmpSurvey()
    {
        $qb = $this->createQueryBuilder('geoname')
                ->join('geoname.questionnaires', 'questionnaire')
                ->join('questionnaire.survey', 'survey')
                ->where('survey.type = :surveyType')
                ->orderBy('geoname.name');

        $qb->setParameter('surveyType', \Application\Model\SurveyType::$JMP);

        return $qb->getQuery()->getResult();
    }

}
