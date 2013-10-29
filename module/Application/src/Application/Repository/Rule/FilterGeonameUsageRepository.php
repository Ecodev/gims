<?php

namespace Application\Repository\Rule;

use Doctrine\Common\Collections\ArrayCollection;

class FilterGeonameUsageRepository extends \Application\Repository\AbstractRepository
{

    private $cache = array();

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
        // If no cache for geoname, fill the cache
        if (!isset($this->cache[$geonameId])) {
            $qb = $this->createQueryBuilder('filterGeonameUsage')
                    ->select('filterGeonameUsage, geoname, filter, rule')
                    ->join('filterGeonameUsage.geoname', 'geoname')
                    ->join('filterGeonameUsage.filter', 'filter')
                    ->join('filterGeonameUsage.rule', 'rule')
                    ->andWhere('filterGeonameUsage.geoname = :geoname')
                    ->orderBy('filterGeonameUsage.sorting, filterGeonameUsage.id')
            ;

            $qb->setParameters(array(
                'geoname' => $geonameId,
            ));

            $res = $qb->getQuery()->getResult();

            // Restructure cache to be [geonameId => [filterId => [partId => value]]]
            foreach ($res as $filterGeonameUsage) {
                $this->cache[$filterGeonameUsage->getGeoname()->getId()][$filterGeonameUsage->getFilter()->getId()][$filterGeonameUsage->getPart()->getId()][] = $filterGeonameUsage;
            }
        }

        if (isset($this->cache[$geonameId][$filterId][$partId]))
            $possible = $this->cache[$geonameId][$filterId][$partId];
        else
            $possible = array();

        // Returns the first non-excluded
        foreach ($possible as $filterGeonameUsage) {
            if (!$excluded->contains($filterGeonameUsage->getRule()))
                return $filterGeonameUsage;
        }

        return null;
    }

}
