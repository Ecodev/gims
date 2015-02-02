<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * FilterSet is used to group filters together and make them available as
 * a choice to plot graphs or output tables.
 * It doesn't have any special meaning for computing.
 * @ORM\Entity(repositoryClass="Application\Repository\FilterSetRepository")
 */
class FilterSet extends AbstractModel implements \Application\Service\RoleContextInterface
{

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    private $name;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Filter", inversedBy="filterSets")
     * @ORM\OrderBy({"sorting" = "ASC", "id" = "ASC"})
     */
    private $filters;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default" = 0})
     */
    private $isPublished = false;

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->filters = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), [
            'name',
        ]);
    }

    /**
     * Set name
     * @param string $name
     * @return self
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
     * Add a filter
     * @param Filter $filter
     * @return self
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
     * @return self
     */
    public function setFilters(\Doctrine\Common\Collections\ArrayCollection $filters)
    {

        // remove filters that are no more in collection
        foreach ($this->getFilters() as $filter) {
            $filter->getFilterSets()->removeElement($this);
        }

        $this->getFilters()->clear();

        // add filters
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }

        return $this;
    }

    /**
     * Returns whether this filter set is published and thus visible to anonymous
     * @return boolean
     */
    public function isPublished()
    {
        return $this->isPublished;
    }

    /**
     * @param boolean $isPublished
     * @return self
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = (bool) $isPublished;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleContext($action)
    {
        // If we don't have ID, that mean we were not saved yet,
        // and we cannot use ourself as context
        if ($this->getId()) {
            return $this;
        } else {
            return null;
        }
    }
}
