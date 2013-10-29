<?php

namespace Application\Repository;

class GeonameRepository extends AbstractRepository
{

    use Traits\OrderedByName;

    private $cache = array();

    /**
     * Returns the Geoname ID for the given questionnaire (and use cache for subsequent usage)
     * @param integer $questionnaireId
     * @return integer
     */
    public function getIdByQuestionnaireId($questionnaireId)
    {

        if (!isset($this->cache[$questionnaireId])) {

            $geonameId = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->createQueryBuilder('questionnaire')
                    ->select('geoname.id')
                    ->join('questionnaire.geoname', 'geoname')
                    ->where('questionnaire = :questionnaire')
                    ->setParameter('questionnaire', $questionnaireId)
                    ->getQuery()
                    ->getSingleScalarResult();
            ;
            $this->cache[$questionnaireId] = $geonameId;
        }

        return $this->cache[$questionnaireId];
    }

}

