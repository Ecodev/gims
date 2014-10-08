<?php

namespace Application\Repository;

use Doctrine\ORM\Query\Expr\Join;

class PopulationRepository extends AbstractChildRepository
{

    public function getAllWithPermission($action = 'read', $search = null, $parentName = null, \Application\Model\AbstractModel $parent = null)
    {
        $qb = $this->createQueryBuilder('population');
        $qb->join('population.country', 'country', Join::WITH);
        $qb->join('population.part', 'part', Join::WITH);
        $qb->join('population.questionnaire', 'questionnaire', Join::WITH);

        $qb->where('population.' . $parentName . ' = :parent');
        $qb->setParameter('parent', $parent);

        $this->addSearch($qb, $search, ['population.year']);

        return $qb->getQuery()->getResult();
    }

    /**
     * @var array $cache [geonameId => [year => [partId => [questionnaireId => population]]]]
     */
    private $cache = array();

    /**
     * Returns the population value for given questionnaire and part.
     * Optimized to fetch all data by geoname.
     * @param \Application\Model\Questionnaire $questionnaire
     * @param integer $partId
     * @return \Application\Model\Population
     */
    public function getPopulationByQuestionnaire(\Application\Model\Questionnaire $questionnaire, $partId)
    {
        return $this->getPopulationByGeoname($questionnaire->getGeoname(), $partId, $questionnaire->getSurvey()->getYear(), $questionnaire->getId());
    }

    /**
     * Returns the population value for given geoname, part and year.
     * Optimized to fetch all data by geoname.
     * @param \Application\Model\Geoname $geoname
     * @param integer $partId
     * @param integer $year
     * @param integer|null $questionnaireId if given will return custom population for that questionnaire if available
     * @return integer
     */
    public function getPopulationByGeoname(\Application\Model\Geoname $geoname, $partId, $year, $questionnaireId = null)
    {
        if (!isset($this->cache[$geoname->getId()])) {
            $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
            $rsm->addScalarResult('population', 'population');
            $rsm->addScalarResult('year', 'year');
            $rsm->addScalarResult('part_id', 'part_id');
            $rsm->addScalarResult('questionnaire_id', 'questionnaire_id');

            $query = $this->getEntityManager()->createNativeQuery('
                SELECT population, year, part_id, questionnaire_id
                FROM population
                INNER JOIN country ON (population.country_id = country.id AND country.geoname_id = :geoname)', $rsm);

            $query->setParameters(array(
                'geoname' => $geoname,
            ));
            $data = $query->getResult();

            // Ensure that we hit the cache next time, even if we have no results at all
            $this->cache[$geoname->getId()] = array();

            foreach ($data as $p) {
                $this->cache[$geoname->getId()][$p['year']][$p['part_id']][$p['questionnaire_id']] = $p['population'];
            }
        }

        // Try to return population specific for this questionnaire, or else default to official population
        if (isset($this->cache[$geoname->getId()][$year][$partId][$questionnaireId])) {
            return $this->cache[$geoname->getId()][$year][$partId][$questionnaireId];
        } else {
            return @$this->cache[$geoname->getId()][$year][$partId][null];
        }
    }

}
