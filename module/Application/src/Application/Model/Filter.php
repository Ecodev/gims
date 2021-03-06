<?php

namespace Application\Model;

use Application\Service\MultipleRoleContext;
use Application\Utility;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use MischiefCollective\ColorJizz\Formats\Hex;

/**
 * A Filter is used to organise things. They are usually defined by the answer
 * value if present, or else the sum of its sub-filters. But it can also have
 * custom rules, such as a list of manually specified filters to sum (summands)
 * or the use of formulas.
 * There are slightly different usage from the end-user point of view:
 * "Low level" filters are used to organise questions in a tree-ish way. This is
 * what used to be called categories in early versions:
 * <pre>
 * Water
 *    Tap water
 *       In house
 *       Public place
 *    Bottled water
 *       Good quality
 *       Bad quality
 * </pre>
 * "High level" filters are used to group other filters at a higher level, and
 * often transversely across the tree above. This what use to be called
 * filterComponent in early versions:
 * <pre>
 * Improved
 *    In house
 *    Good quality
 * Unimproved
 *    Public place
 *    Bad quality
 * </pre>
 * @ORM\Entity(repositoryClass="Application\Repository\FilterRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Filter extends AbstractModel implements Rule\ReferencableInterface
{

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    private $name;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Filter", inversedBy="parents")
     * @ORM\OrderBy({"sorting" = "ASC", "id" = "ASC"})
     * @ORM\JoinTable(name="filter_children",
     *      inverseJoinColumns={@ORM\JoinColumn(name="child_filter_id", onDelete="CASCADE")}
     *      )
     */
    private $children;

    /**
     * @ORM\ManyToMany(targetEntity="Filter", mappedBy="children")
     * @ORM\OrderBy({"sorting" = "ASC", "id" = "ASC"})
     */
    private $parents;

    /**
     * Questions that have current filter assigned
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\Application\Model\Question\AbstractAnswerableQuestion", mappedBy="filter")
     */
    private $questions;

    /**
     * FilterSets that have current filter assigned
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="\Application\Model\FilterSet", mappedBy="filters")
     */
    private $filterSets;

    /**
     * Summands are the filters which must be summed to compute this filter value
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Filter")
     * @ORM\JoinTable(name="filter_summand",
     *      inverseJoinColumns={@ORM\JoinColumn(name="summand_filter_id", onDelete="CASCADE")}
     *      )
     */
    private $summands;

    /**
     * Additional rules to apply to compute value
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\Application\Model\Rule\FilterQuestionnaireUsage", mappedBy="filter")
     * @ORM\OrderBy({"isSecondStep" = "DESC", "sorting" = "ASC", "id" = "ASC"})
     */
    private $filterQuestionnaireUsages;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $color;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $bgColor;

    /**
     * @var int
     * @ORM\Column(type="boolean", nullable=false, options={"default" = 0})
     */
    private $isThematic = false;

    /**
     * @var thematicFilter
     * @ORM\ManyToOne(targetEntity="Filter")
     * @ORM\JoinColumn(nullable=true)
     */
    private $thematicFilter;

    /**
     * @var integer
     * @ORM\Column(type="smallint", nullable=false, options={"default" = 0})
     */
    private $sorting = 0;

    /**
     * Specifies if the filter is THE NSA container
     * @var int
     * @ORM\Column(type="boolean", nullable=false, options={"default" = 0})
     */
    private $isNsa = false;

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->filterQuestionnaireUsages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->parents = new \Doctrine\Common\Collections\ArrayCollection();
        $this->summands = new \Doctrine\Common\Collections\ArrayCollection();
        $this->questions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->filterSets = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Get children
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get all children recursively
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAllChildren()
    {
        $children = $this->getChildren()->toArray();
        foreach ($children as $child) {
            $children = array_merge($children, $child->getAllChildren()->toArray());
        }

        return new ArrayCollection($children);
    }

    /**
     * Add a child
     * @param Filter $child
     * @return self
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
     * Set new filters, replacing entirely existing children
     * @param \Doctrine\Common\Collections\ArrayCollection $children
     * @return self
     */
    public function setChildren(\Doctrine\Common\Collections\ArrayCollection $children)
    {
        foreach ($this->getChildren() as $child) {
            $child->getParents()->removeElement($this);
        }

        $this->getChildren()->clear();

        foreach ($children as $child) {
            $this->addChild($child);
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
     * @return self
     */
    protected function parentAdded(Filter $parent)
    {
        if (!$this->getParents()->contains($parent)) {
            $this->getParents()->add($parent);
        }

        return $this;
    }

    /**
     * Set new filters, replacing entirely existing parents
     * @param \Doctrine\Common\Collections\ArrayCollection $parents
     * @return self
     */
    public function setParents(\Doctrine\Common\Collections\ArrayCollection $parents)
    {
        foreach ($this->getParents() as $parent) {
            $parent->getChildren()->removeElement($this);
        }

        $this->getParents()->clear();

        foreach ($parents as $parent) {
            $parent->addChild($this);
        }

        return $this;
    }

    /**
     * Returns FilterSet
     */
    public function getFilterSets()
    {
        return $this->filterSets;
    }

    /**
     * Set FilterSets
     */
    public function setFilterSets(ArrayCollection $filterSets)
    {
        foreach ($this->getFilterSets() as $filterSet) {
            $filterSet->getFilters()->removeElement($this);
        }

        $this->getFilterSets()->clear();

        foreach ($filterSets as $filterSet) {
            $filterSet->addFilter($this);
        }

        return $this;
    }

    /**
     * Notify the filter that he has a new filterset.
     * This should only be called by FilterSet::addFilter()
     * @param FilterSet $filterSet
     * @return self
     */
    public function filterSetAdded(FilterSet $filterSet)
    {
        if (!$this->getFilterSets()->contains($filterSet)) {
            $this->getFilterSets()->add($filterSet);
        }

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
     * @return self
     */
    public function addSummand(Filter $summand)
    {
        if (!$this->getSummands()->contains($summand)) {
            $this->getSummands()->add($summand);
        }

        return $this;
    }

    /**
     * Set new summands, replacing entirely existing summands
     * @param \Doctrine\Common\Collections\ArrayCollection $summands
     * @return self
     */
    public function setSummands(\Doctrine\Common\Collections\ArrayCollection $summands)
    {
        $this->getSummands()->clear();
        foreach ($summands as $summand) {
            $this->addSummand($summand);
        }

        return $this;
    }

    /**
     * Return all questions that are associated to this filter
     * @param $survey \Application\Model\Survey restrict questions for given survey
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getQuestions(\Application\Model\Survey $survey = null)
    {
        $questions = new ArrayCollection();
        if ($survey) {
            foreach ($this->questions as $question) {
                if ($question->getSurvey() === $survey) {
                    $questions->add($question);
                }
            }
        } else {
            $questions = $this->questions;
        }

        return $questions;
    }

    /**
     * Add question to collection
     * @param Question\AbstractAnswerableQuestion $question
     */
    public function addQuestion(\Application\Model\Question\AbstractAnswerableQuestion $question)
    {
        if (!$this->getQuestions()->contains($question)) {
            $this->getQuestions()->add($question);
        }
    }

    /**
     * Remove question to collection
     * @param Question\AbstractAnswerableQuestion $question
     */
    public function removeQuestion(\Application\Model\Question\AbstractAnswerableQuestion $question)
    {
        $this->getQuestions()->removeElement($question);
    }

    /**
     * Get rules
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFilterQuestionnaireUsages()
    {
        return $this->filterQuestionnaireUsages;
    }

    /**
     * Notify the filter that it was added to FilterQuestionnaireUsage relation.
     * This should only be called by FilterQuestionnaireUsage::setFilter()
     * @param Rule\FilterQuestionnaireUsage $usage
     * @return self
     */
    public function filterQuestionnaireUsageAdded(Rule\FilterQuestionnaireUsage $usage)
    {
        $this->getFilterQuestionnaireUsages()->add($usage);

        return $this;
    }

    /**
     * Return color with generic replacement if no color in database
     * @param Ration|int saturation from 0 to 100
     * @return string
     */
    public function getGenericColor($ratio = 100)
    {
        if (!$this->color) {
            $color = Utility::getColor($this->getId(), $ratio);
        } else {
            $color = $this->getColor($ratio);
        }

        return $color;
    }

    /**
     * Get color if set in database
     * @param Ratio|int saturation from 0 to 100
     * @return string
     */
    public function getColor($ratio = 100)
    {
        if ($ratio == 100) {
            $color = $this->color;
        } else {
            $hex = new Hex(intval(str_replace('#', '0x', strtoupper($this->color)), 16)); //Create Hex object
            $hsv = $hex->toHSV(); // then transform to HSV
            $hsv->saturation *= $ratio / 100; //multiply saturation by ratio
            $color = '#' . $hsv->toHex(); // and then transform again to Hex
        }

        return $color;
    }

    /**
     * @param int $ratio
     */
    public function getTextColor($ratio = 100)
    {
        return Utility::getLisibleColor($this->getColor($ratio));
    }

    public function getGenericTextColor($ratio = 100)
    {
        return Utility::getLisibleColor($this->getGenericColor($ratio));
    }

    /**
     * Set color in database
     * @param $color string hexadecimal
     * @return self
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get background color i
     * @return string
     */
    public function getBgColor()
    {
        return $this->bgColor;
    }

    /**
     * Set color in database
     * @param $color string hexadecimal
     * @return self
     */
    public function setBgColor($color)
    {
        $this->bgColor = $color;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleContext($action)
    {
        $contexts = new MultipleRoleContext($this->getFilterSets());
        foreach ($this->getParents() as $parent) {
            $contexts->merge($parent->getRoleContext($action));
        }

        // If we try to delete a filter, we must also consider the side-effect it may have on Rules that use this filter
        if ($action == 'delete') {
            $repository = \Application\Module::getEntityManager()->getRepository(\Application\Model\Rule\Rule::class);
            $rulesWithReference = $repository->getAllReferencing($this);
            foreach ($rulesWithReference as $rule) {
                $contexts->merge($rule->getRoleContext($action));
            }
        }

        return $contexts->count() ? $contexts : null;
    }

    /**
     * @return bool
     */
    public function isThematic()
    {
        return $this->isThematic;
    }

    /**
     * @param bool $isThematic
     *      * @return self
     */
    public function setIsThematic($isThematic)
    {
        $this->isThematic = $isThematic;

        return $this;
    }

    /**
     * return \Application\Model\Filter
     */
    public function getThematicFilter()
    {
        return $this->thematicFilter;
    }

    /**
     * @param \Application\Model\Filter $thematicFilter
     * @throws InvalidArgumentException
     * @return self
     */
    public function setThematicFilter($thematicFilter)
    {
        if ($thematicFilter->isThematic()) {
            $this->thematicFilter = $thematicFilter;
        } else {
            throw new InvalidArgumentException('Filter ' . $thematicFilter->getName() . ' is not a thematic.');
        }

        return $this;
    }

    /**
     * @param int $sorting
     * @return self
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * @return int
     */
    public function getSorting()
    {
        return $this->sorting;
    }

    /**
     * Automatically called by Doctrine when the object is modified whatsoever to invalid computing cache
     * @ORM\PostPersist
     * @ORM\PreUpdate
     * @ORM\PreRemove
     */
    public function invalidateCache()
    {
        $key = 'filter:' . $this->getId();
        $cache = \Application\Module::getServiceManager()->get('Calculator\Cache');
        $cache->removeItem($key);
    }

    /**
     * @return int
     */
    public function isNsa()
    {
        return $this->isNsa;
    }

    /**
     * @param int $isNSA
     * @return self
     */
    public function setIsNSA($isNsa)
    {
        $this->isNsa = $isNsa;

        return $this;
    }

    public function getSymbol()
    {
        return 'F';
    }
}
