<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Part is used to "split" a unique question to have several answers.
 * Typical parts would be Urban, Rural and Total. In the future it could be a finer
 * division such as "< 1'000 people", "< 10'000 people", "100'000 people", etc.
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

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default" = 0})
     */
    private $isTotal = false;

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->setName($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'name',
        ));
    }

    /**
     * Set name
     *
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
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the read-only property whether this part represent the total
     * @return boolean
     */
    public function isTotal()
    {
        return $this->isTotal;
    }

}
