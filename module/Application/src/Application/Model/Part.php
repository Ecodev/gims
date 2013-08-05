<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Part is used to "split" a unique question to have several answers.
 * Typical parts would be Urban and Rural. In the future it could be a finer
 * division such as "< 1'000 people", "< 10'000 people", "100'000 people", etc.
 *
 * "Total" part does not exist, as the total is defined by the absence of part (NULL).
 *
 * @ORM\Entity(repositoryClass="Application\Repository\PartRepository")
 */
class Part extends AbstractModel
{

    /**
     * @var array
     */
    protected static $jsonConfig
        = array(
            'name'
        );

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
