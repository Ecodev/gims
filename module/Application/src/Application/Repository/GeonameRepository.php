<?php

namespace Application\Repository;

class GeonameRepository extends AbstractRepository
{

    private $cache = array();

    public function getAllWithPermission($action = 'read', $search = null)
    {
        $qb = $this->createQueryBuilder('geoname')
                ->orderBy('geoname.name');

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

}
