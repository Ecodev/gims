<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * CategoryFilterComponent
 *
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryFilterComponentRepository")
 */
class CategoryFilterComponent extends AbstractModel
{

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $name;

    /**
     * Categories which must be summed to compute this filter value
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Category")
     */
    private $categories;

    /**
     * Additional rules to apply to compute value
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="\Application\Model\Rule\CategoryFilterComponentRule", mappedBy="categoryFilterComponent")
     */
    private $categoryFilterComponentRules;

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->categoryFilterComponentRules = new \Doctrine\Common\Collections\ArrayCollection();
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setName($name);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return CategoryFilterComponent
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
     * Get categories
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Add a category
     * @param Category $category
     * @return Category
     */
    public function addCategory(Category $category)
    {
        if (!$this->getCategories()->contains($category)) {
            $this->getCategories()->add($category);
        }

        return $this;
    }

    /**
     * Get rules
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCategoryFilterComponentRules()
    {
        return $this->categoryFilterComponentRules;
    }

    /**
     * Notify the user that he was added to UserSurvey relation.
     * This should only be called by Rule::setCategoryFilterComponent()
     * @param Rule\CategoryFilterComponentRule $rule
     * @return CategoryFilterComponent
     */
    public function ruleAdded(Rule\CategoryFilterComponentRule $rule)
    {
        $this->getCategoryFilterComponentRules()->add($rule);

        return $this;
    }

}
