<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * FilterSet is used to group filters together and make them available as
 * a choice to plot graphs or output tables.
 * It doesn't have any special meaning for computing.
 * @ORM\Entity(repositoryClass="Application\Repository\FilterSetRepository")
 */
class FilterSet extends AbstractModel
{

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    private $name;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Filter")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $filters;

    /**
     * @var FilterSet
     * @ORM\ManyToOne(targetEntity="FilterSet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="SET NULL")
     * })
     */
    private $originalFilterSet;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Filter")
     * @ORM\JoinTable(name="filter_set_excluded_filter",
     *      inverseJoinColumns={@ORM\JoinColumn(name="excluded_filter_id")}
     *      )
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $excludedFilters;

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->filters = new \Doctrine\Common\Collections\ArrayCollection();
        $this->excludedFilters = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setName($name);
    }

    /**
     * @inheritdoc
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'name',
        ));
    }

    /**
     * Set name
     * @param string $name
     * @return FilterSet
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get filters
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Get excluded filters
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getExcludedFilters()
    {
        return $this->excludedFilters;
    }

    /**
     * Add a filter
     * @param Filter $filter
     * @return FilterSet
     */
    public function addFilter(Filter $filter)
    {
        if (!$this->getFilters()->contains($filter)) {
            $this->getFilters()->add($filter);
            $filter->filterSetAdded($this);
        }

        return $this;
    }

    /**
     * Set new filters, replacing entirely existing filters
     * @param \Doctrine\Common\Collections\ArrayCollection $filters
     * @return $this
     */
    public function setFilters(\Doctrine\Common\Collections\ArrayCollection $filters)
    {
        $this->getFilters()->clear();

        // Clean up the collection from old choices
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }

        return $this;
    }

    /**
     * Add a filter
     * @param Filter $filter
     * @return FilterSet
     */
    public function addExcludedFilter(Filter $filter)
    {
        if (!$this->getExcludedFilters()->contains($filter)) {
            $this->getExcludedFilters()->add($filter);
        }

        return $this;
    }

    /**
     * Set originalFilterSet from which this filter set was copied
     * @param FilterSet $originalFilterSet
     * @return FilterSet
     */
    public function setOriginalFilterSet(FilterSet $originalFilterSet = null)
    {
        $this->originalFilterSet = $originalFilterSet;

        return $this;
    }

    /**
     * Get originalFilterSet from which this filter set was copied
     * @return FilterSet
     */
    public function getOriginalFilterSet()
    {
        return $this->originalFilterSet;
    }

}
