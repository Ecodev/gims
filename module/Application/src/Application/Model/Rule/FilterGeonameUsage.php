<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;
use Application\Model\Filter;
use Application\Model\Geoname;

/**
 * FilterGeonameUsage allows us to "apply" a formula to a filter-part couple, to be used for regression computation.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\Rule\FilterGeonameUsageRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="filter_geoname_usage_unique",columns={"filter_id", "geoname_id", "part_id", "rule_id"})})
 */
class FilterGeonameUsage extends AbstractUsage
{

    /**
     * @var Filter
     *
     * @ORM\ManyToOne(targetEntity="Application\Model\Filter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $filter;

    /**
     * @var Geoname
     * @ORM\ManyToOne(targetEntity="Application\Model\Geoname", inversedBy="filterGeonameUsages")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $geoname;

    /**
     * @inheritdoc
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'filter',
            'geoname',
        ));
    }

    /**
     * Set filter
     *
     * @param Filter $filter
     * @return FilterGeonameUsage
     */
    public function setFilter(Filter $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Get filter
     *
     * @return \Application\Model\Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set geoname
     *
     * @param Geoname $geoname
     *
     * @return Questionnaire
     */
    public function setGeoname(Geoname $geoname)
    {
        $this->geoname = $geoname;
        $geoname->filterGeonameUsageAdded($this);

        return $this;
    }

    /**
     * Get geoname
     *
     * @return Geoname
     */
    public function getGeoname()
    {
        return $this->geoname;
    }

}
