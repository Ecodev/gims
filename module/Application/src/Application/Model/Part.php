<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Part
 *
 * @ORM\Entity(repositoryClass="Application\Repository\PartRepository")
 */
class Part extends AbstractModel
{

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $name;

    public function __construct($name = null)
    {
        $this->setName($name);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Part
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

}
