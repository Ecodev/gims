<?php

namespace Application\Repository;

class PopulationRepository extends AbstractRepository
{

    /**
     * @var array $cache [geonameId => [year => [partId => population]]]
     */
    private $cache = array();

    /**
     * Returns the population for given questionnaire and part.
     * Optimized to fetch all data by geoname.
     * @param \Application\Model\Questionnaire $questionnaire
     * @param integer $partId
     * @return \Application\Model\Population
     */
    public function getOneByQuestionnaire(\Application\Model\Questionnaire $questionnaire, $partId)
    {

        return $this->getOneByGeoname($questionnaire->getGeoname(), $partId, $questionnaire->getSurvey()->getYear());
    }

    /**
     * Returns the population for given geoname, part and year.
     * * Optimized to fetch all data by geoname.
     * @param \Application\Model\Geoname $geoname
     * @param integer $partId
     * @param integer $year
     * @return \Application\Model\Population
     */
    public function getOneByGeoname(\Application\Model\Geoname $geoname, $partId, $year)
    {

        if (!isset($this->cache[$geoname->getId()])) {

            $query = $this->getEntityManager()->createQuery("SELECT p FROM Application\Model\Population p
                JOIN p.country c WITH c.geoname = :geoname"
            );

            $query->setParameters(array(
                'geoname' => $geoname,
            ));

            foreach ($query->getResult() as $p) {
                $this->cache[$geoname->getId()][$p->getYear()][$p->getPart()->getId()] = $p;
            }
        }

        return $this->cache[$geoname->getId()][$year][$partId];
    }

}
