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
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="CategoryFilterComponent")
     */
    private $categoryfiltercomponents;

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->categoryfiltercomponents = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Get categoryfiltercomponents
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCategoryFilterComponents()
    {
        return $this->categoryfiltercomponents;
    }

    /**
     * Add a categoryfiltercomponent
     * @param Categoryfiltercomponent $categoryfiltercomponent
     * @return Filter
     */
    public function addCategoryFilterComponent(Categoryfiltercomponent $categoryfiltercomponent)
    {
        if (!$this->getCategoryFilterComponents()->contains($categoryfiltercomponent)) {
            $this->getCategoryFilterComponents()->add($categoryfiltercomponent);
        }

        return $this;
    }

}
