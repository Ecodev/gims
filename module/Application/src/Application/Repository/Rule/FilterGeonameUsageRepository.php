<?php

namespace Application\Repository\Rule;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr\Join;

class FilterGeonameUsageRepository extends \Application\Repository\AbstractRepository
{

    public function getAllWithPermission($action = 'read', $search = null, $parentName = null, \Application\Model\AbstractModel $parent = null)
    {
        $qb = $this->createQueryBuilder('fgu');
        $qb->join('fgu.rule', 'rule', Join::WITH);
        $qb->join('fgu.filter', 'filter', Join::WITH);
        $qb->join('fgu.geoname', 'geoname', Join::WITH);
        $qb->join('fgu.part', 'part', Join::WITH);

        $qb->where('fgu.' . $parentName . ' = :parent');
        $qb->setParameter('parent', $parent);

        $this->addSearch($qb, $search, array('filter.name', 'rule.name', 'rule.formula', 'geoname.name', 'part.name'));

        return $qb->getQuery()->getResult();
    }

    /**
     * @var array $cache [geonameId => [filterId => [partId => value]]]
     */
    private $cache = array();

    /**
     * Initates cache
     * @param $geonameId
     */
    private function getAll($geonameId)
    {

        // If no cache for geoname, fill the cache
        if (!isset($this->cache[$geonameId])) {
            $qb = $this->createQueryBuilder('filterGeonameUsage')
                    ->select('filterGeonameUsage, geoname, filter, rule')
                    ->join('filterGeonameUsage.geoname', 'geoname')
                    ->join('filterGeonameUsage.filter', 'filter')
                    ->join('filterGeonameUsage.rule', 'rule')
                    ->andWhere('filterGeonameUsage.geoname = :geoname')
                    ->orderBy('filterGeonameUsage.sorting, filterGeonameUsage.id');

            $qb->setParameters(array(
                'geoname' => $geonameId,
            ));

            $res = $qb->getQuery()->getResult();

            // Ensure that we hit the cache next time, even if we have no results at all
            $this->cache[$geonameId] = array();

            // Restructure cache to be [geonameId => [filterId => [partId => value]]]
            foreach ($res as $filterGeonameUsage) {
                $this->cache[$filterGeonameUsage->getGeoname()->getId()][$filterGeonameUsage->getFilter()->getId()][$filterGeonameUsage->getPart()->getId()][] = $filterGeonameUsage;
            }
        }
    }

    /**
     * Return all the rules according to parameters given
     * @param $geoname
     * @param $filter
     * @param $part
     * @return Array
     */
    public function getAllForGeonameAndFilter($geoname, $filter, $part)
    {
        $this->getAll($geoname->getId());

        return isset($this->cache[$geoname->getId()][$filter->getId()][$part->getId()]) ? $this->cache[$geoname->getId()][$filter->getId()][$part->getId()] : [];
    }

    /**
     * Return the first FilterGeonameUsage
     * @param integer $geonameId
     * @param integer $filterId
     * @param integer $partId
     * @param \Doctrine\Common\Collections\ArrayCollection $excluded
     * @return FilterGeonameUsage|null
     */
    public function getFirst($geonameId, $filterId, $partId, ArrayCollection $excluded)
    {
        $this->getAll($geonameId);

        if (isset($this->cache[$geonameId][$filterId][$partId])) {
            $possible = $this->cache[$geonameId][$filterId][$partId];
        } else {
            $possible = array();
        }

        // Returns the first non-excluded
        foreach ($possible as $filterGeonameUsage) {
            if (!$excluded->contains($filterGeonameUsage->getRule())) {
                return $filterGeonameUsage;
            }
        }

        return null;
    }

}
