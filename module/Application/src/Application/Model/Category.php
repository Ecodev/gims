<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 *
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryRepository")
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
    private $official;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    private $officialCategory;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $parent;

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
        $this->parent = $parent;

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

}
