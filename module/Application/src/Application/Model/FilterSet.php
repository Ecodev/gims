<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * FilterSet is used to group filters together and make them available as
 * a choice to plot graphs or output tables.
 *
 * It doesn't have any special meaning for computing.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\FilterSetRepository")
 */
class FilterSet extends AbstractModel
{

    /**
     * @var array
     */
    protected static $jsonConfig
        = array('name');

    /**
     * @var array
     */
    protected static $relationProperties
        = array(
            'filters'         => '\Application\Model\Filter',
            'excludedFilters' => '\Application\Model\Filter',
        );

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $name;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Filter")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $filters;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Filter")
     * @ORM\JoinTable(name="filter_set_excluded_filter",
     *      inverseJoinColumns={@ORM\JoinColumn(name="excluded_filter_id")}
     *      )
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $excludedFilters;

    /**
     * Constructor
     *
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->filters = new \Doctrine\Common\Collections\ArrayCollection();
        $this->excludedFilters = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setName($name);
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return FilterSet
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get filters
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Get excluded filters
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getExcludedFilters()
    {
        return $this->excludedFilters;
    }

    /**
     * Add a filter
     *
     * @param Filter $filter
     *
     * @return FilterSet
     */
    public function addFilter(Filter $filter)
    {
        if (!$this->getFilters()->contains($filter)) {
            $this->getFilters()->add($filter);
        }

        return $this;
    }

    /**
     * Add a filter
     *
     * @param Filter $filter
     *
     * @return FilterSet
     */
    public function addExcludedFilter(Filter $filter)
    {
        if (!$this->getExcludedFilters()->contains($filter)) {
            $this->getExcludedFilters()->add($filter);
        }

        return $this;
    }

}
