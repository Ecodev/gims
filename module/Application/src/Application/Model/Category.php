<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 *
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="category_unique",columns={"name", "parent_id"})})
 */
class Category extends AbstractModel
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
    private $official = false;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="SET NULL")
     * })
     */
    private $officialCategory;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="children")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $parent;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     */
    private $children;

    public function __construct($name = null)
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setName($name);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Category
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
     * @param boolean $official
     * @return Category
     */
    public function setOfficial($official)
    {
        $this->official = $official;

        return $this;
    }

    /**
     * Get official
     *
     * @return boolean
     */
    public function getOfficial()
    {
        return $this->official;
    }

    /**
     * Set officialCategory
     *
     * @param Category $officialCategory
     * @return Category
     */
    public function setOfficialCategory(Category $officialCategory = null)
    {
        $this->officialCategory = $officialCategory;

        return $this;
    }

    /**
     * Get officialCategory
     *
     * @return Category
     */
    public function getOfficialCategory()
    {
        return $this->officialCategory;
    }

    /**
     * Set parent
     *
     * @param Category $parent
     * @return Category
     */
    public function setParent(Category $parent = null)
    {
        $oldParent = $this->getParent();

        if ($oldParent)
            $oldParent->childRemoved($this);

        $this->parent = $parent;

        if ($parent)
            $parent->childAdded($this);

        return $this;
    }

    /**
     * Get parent
     *
     * @return Category
     */
    public function getParent()
    {
        return $this->parent;
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
     * Notify the category that he has a new child.
     * This should only be called by Category::setParent()
     * @param Category $category
     * @return Category
     */
    private function childAdded(Category $category)
    {
        $this->getChildren()->add($category);

        return $this;
    }

    /**
     * Notify the category that he has lost a child.
     * This should only be called by Category::setParent()
     * @param Category $category
     * @return Category
     */
    public function childRemoved(Category $category)
    {
        $this->getChildren()->removeElement($category);

        return $this;
    }

}
