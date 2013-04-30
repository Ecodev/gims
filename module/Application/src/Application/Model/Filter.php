<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Filter
 *
 * @ORM\Entity(repositoryClass="Application\Repository\FilterRepository")
 */
class Filter extends AbstractModel
{

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isOfficial = false;

    /**
     * @var Filter
     *
     * @ORM\ManyToOne(targetEntity="Filter")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="SET NULL")
     * })
     */
    private $officialFilter;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Filter", inversedBy="parents")
     * @ORM\OrderBy({"name" = "ASC"})
     * @ORM\JoinTable(name="filter_children",
     *      inverseJoinColumns={@ORM\JoinColumn(name="child_filter_id")}
     *      )
     */
    private $children;

    /**
     * @ORM\ManyToMany(targetEntity="Filter", mappedBy="children")
     */
    private $parents;

    /**
     * Summands are the filters which must be summed to compute this filter value
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Filter")
     * @ORM\JoinTable(name="filter_summand",
     *      inverseJoinColumns={@ORM\JoinColumn(name="summand_filter_id")}
     *      )
     */
    private $summands;

    /**
     * Additional rules to apply to compute value
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="\Application\Model\Rule\FilterRule", mappedBy="filter")
     */
    private $filterRules;

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->filterRules = new \Doctrine\Common\Collections\ArrayCollection();
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->parents = new \Doctrine\Common\Collections\ArrayCollection();
        $this->summands = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setName($name);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Filter
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
     * Set official
     *
     * @param boolean $isOfficial
     * @return Filter
     */
    public function setIsOfficial($isOfficial)
    {
        $this->isOfficial = $isOfficial;

        return $this;
    }

    /**
     * Get official
     *
     * @return boolean
     */
    public function isOfficial()
    {
        return $this->isOfficial;
    }

    /**
     * Set officialFilter
     *
     * @param Filter $officialFilter
     * @return Filter
     */
    public function setOfficialFilter(Filter $officialFilter = null)
    {
        // If there is an official filter, then this filter is not official,
        //  but opposite may not be true, we could have a non-official filter, not yet linked to official one
        if ($officialFilter) {
            $this->setIsOfficial(false);

            foreach ($officialFilter->getParents() as $parent) {
                $parent->addChild($this);
            }
        }

        $this->officialFilter = $officialFilter;

        return $this;
    }

    /**
     * Get officialFilter
     *
     * @return Filter
     */
    public function getOfficialFilter()
    {
        return $this->officialFilter;
    }

    /**
     * Get children
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add a child
     * @param Filter $child
     * @return Filter
     */
    public function addChild(Filter $child)
    {
        if (!$this->getChildren()->contains($child)) {
            $this->getChildren()->add($child);
            $child->parentAdded($this);
        }

        return $this;
    }

    /**
     * Get parents
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * Notify the child that he has a new parent.
     * This should only be called by Filter::addChild()
     * @param Filter $parent
     * @return Filter
     */
    protected function parentAdded(Filter $parent)
    {
        $this->getParents()->add($parent);

        return $this;
    }

    /**
     * Get summands
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getSummands()
    {
        return $this->summands;
    }

    /**
     * Add a summand
     * @param Filter $summand
     * @return Filter
     */
    public function addSummand(Filter $summand)
    {
        if (!$this->getSummands()->contains($summand)) {
            $this->getSummands()->add($summand);
        }

        return $this;
    }

    /**
     * Get rules
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFilterRules()
    {
        return $this->filterRules;
    }

    /**
     * Notify the user that he was added to UserSurvey relation.
     * This should only be called by Rule::setFilter()
     * @param Rule\FilterRule $rule
     * @return Filter
     */
    public function ruleAdded(Rule\FilterRule $rule)
    {
        $this->getFilterRules()->add($rule);

        return $this;
    }

}
