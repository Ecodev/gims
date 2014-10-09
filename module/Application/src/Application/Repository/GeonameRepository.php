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
    public function getSectorFilter($geonameId)
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
            WHERE s.type = \'nsa\'
            AND qu.geoname_id = :geoname
        ', $rsm);

        $n->setParameter('geoname', $geonameId);
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
        // Here we have to also include country even if we don't use it, otherwise
        // Doctrine would issue another SQL query for each geoname to get their countries
        $qb = $this->createQueryBuilder('geoname')
                ->select('geoname', 'country', 'children', 'childrenCountry', 'grandChildren', 'grandChildrenCountry')
                ->leftJoin('geoname.country', 'country', Join::WITH)
                ->leftJoin('geoname.children', 'children', Join::WITH)
                ->leftJoin('children.country', 'childrenCountry', Join::WITH)
                ->leftJoin('children.children', 'grandChildren', Join::WITH)
                ->leftJoin('grandChildren.country', 'grandChildrenCountry', Join::WITH)
                ->where('geoname IN (:geonames)')
                ->orderBy('geoname.name');

        $qb->setParameter('geonames', $geonameIds);

        return $qb->getQuery()->getResult();
    }

}
