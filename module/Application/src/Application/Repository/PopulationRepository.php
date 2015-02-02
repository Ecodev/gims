<?php

namespace Application\Repository;

use Doctrine\ORM\Query\Expr\Join;

class PopulationRepository extends AbstractChildRepository
{

    public function getAllWithPermission($action = 'read', $search = null, $parentName = null, \Application\Model\AbstractModel $parent = null)
    {
        $qb = $this->createQueryBuilder('population');
        $qb->join('population.geoname', 'geoname', Join::WITH);
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
    private $cache = [];

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
            $this->fillPopulationCache([$geoname]);
        }

        // Try to return population specific for this questionnaire, or else default to official population
        if (isset($this->cache[$geoname->getId()][$year][$partId][$questionnaireId])) {
            return $this->cache[$geoname->getId()][$year][$partId][$questionnaireId];
        } else {
            return @$this->cache[$geoname->getId()][$year][$partId][null];
        }
    }

    /**
     * Fill the cache of population for given geonames.
     *
     * Useful if we know in advance we will need to access population from
     * several geonames, then we can load all of them in a single query
     * @param \Application\Model\Geoname[] $geonames
     */
    public function fillPopulationCache(array $geonames)
    {
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $rsm->addScalarResult('geoname_id', 'geoname_id');
        $rsm->addScalarResult('population', 'population');
        $rsm->addScalarResult('year', 'year');
        $rsm->addScalarResult('part_id', 'part_id');
        $rsm->addScalarResult('questionnaire_id', 'questionnaire_id');

        $query = $this->getEntityManager()->createNativeQuery('
                SELECT geoname_id, population, year, part_id, questionnaire_id
                FROM population
                WHERE population.geoname_id IN (:geonames)', $rsm);

        $query->setParameters([
            'geonames' => $geonames,
        ]);
        $data = $query->getResult();

        // Ensure that we hit the cache next time, even if we have no results at all
        foreach ($geonames as $geoname) {
            $this->cache[$geoname->getId()] = [];
        }

        foreach ($data as $p) {
            $this->cache[$p['geoname_id']][$p['year']][$p['part_id']][$p['questionnaire_id']] = $p['population'];
        }
    }

    /**
     * Return geoname populations by part for all years
     * @param \Application\Model\Geoname $geoname
     * @return array
     */
    public function getAllYearsForGeonameByPart(\Application\Model\Geoname $geoname)
    {
        $parts = $this->getEntityManager()
                ->getRepository('\Application\Model\Part')
                ->findAll();
        $yearStart = 1980;
        $yearEnd = 2020;

        $populations = [];
        for ($currentYear = $yearStart; $currentYear <= $yearEnd; $currentYear++) {
            $populations[$currentYear] = [];
            foreach ($parts as $part) {
                $populations[$currentYear][$part->getId()] = $this->getPopulationByGeoname($geoname, $part->getId(), $currentYear);
            }
        }

        return $populations;
    }

    /**
     * Update or create a Population and returns it
     * @param \Application\Model\Geoname $geoname
     * @param \Application\Model\Part $part
     * @param integer $year
     * @param integer $population
     * @return \Application\Model\Population
     */
    public function updateOrCreate(\Application\Model\Geoname $geoname, \Application\Model\Part $part, $year, $population)
    {
        $populationObject = $this->findOneBy([
            'year' => $year,
            'geoname' => $geoname,
            'part' => $part,
        ]);

        if (!$populationObject) {
            $populationObject = new \Application\Model\Population();
            $this->getEntityManager()->persist($populationObject);
            $populationObject->setYear($year);
            $populationObject->setGeoname($geoname);
            $populationObject->setPart($part);
        }
        $populationObject->setPopulation($population);

        return $populationObject;
    }
}
