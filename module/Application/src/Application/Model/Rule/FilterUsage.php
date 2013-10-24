<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * FilterUsage allows us to "apply" a formula to a filter-part couple, to be used for regression computation.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\Rule\FilterUsageRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="filter_usage_unique",columns={"filter_id", "part_id", "rule_id"})})
 */
class FilterUsage extends AbstractUsage
{

    /**
     * @var Filter
     *
     * @ORM\ManyToOne(targetEntity="Application\Model\Filter", inversedBy="filterUsages")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $filter;

    /**
     * Set filter
     *
     * @param Filter $filter
     * @return FilterUsage
     */
    public function setFilter(\Application\Model\Filter $filter)
    {
        $this->filter = $filter;
        $filter->filterUsageAdded($this);

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

}
