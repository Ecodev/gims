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
     * Returns the population for given questionnaire and part.
     * Optimized to fetch all data by geoname.
     * @param \Application\Model\Questionnaire $questionnaire
     * @param integer $partId
     * @return \Application\Model\Population
     */
    public function getOneByQuestionnaire(\Application\Model\Questionnaire $questionnaire, $partId)
    {
        return $this->getOneByGeoname($questionnaire->getGeoname(), $partId, $questionnaire->getSurvey()->getYear(), $questionnaire->getId());
    }

    /**
     * Returns the population for given geoname, part and year.
     * Optimized to fetch all data by geoname.
     * @param \Application\Model\Geoname $geoname
     * @param integer $partId
     * @param integer $year
     * @param integer|null $questionnaireId if given will return custom population for that questionnaire if available
     * @return \Application\Model\Population
     */
    public function getOneByGeoname(\Application\Model\Geoname $geoname, $partId, $year, $questionnaireId = null)
    {
        if (!isset($this->cache[$geoname->getId()])) {

            $query = $this->getEntityManager()->createQuery("SELECT p FROM Application\Model\Population p
                JOIN p.country c WITH c.geoname = :geoname"
            );

            $query->setParameters(array(
                'geoname' => $geoname,
            ));

            // Ensure that we hit the cache next time, even if we have no results at all
            $this->cache[$geoname->getId()] = array();

            foreach ($query->getResult() as $p) {
                $this->cache[$geoname->getId()][$p->getYear()][$p->getPart()->getId()][$p->getQuestionnaire() ? $p->getQuestionnaire()->getId() : null] = $p;
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
