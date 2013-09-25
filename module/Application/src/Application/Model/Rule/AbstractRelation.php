<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * Common properties to "apply" a rule to something
 * @ORM\MappedSuperclass
 */
abstract class AbstractRelation extends \Application\Model\AbstractModel
{

    /**
     * @var Part
     *
     * @ORM\ManyToOne(targetEntity="Application\Model\Part")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $part;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $justification;

    /**
     * Set part
     *
     * @param \Application\Model\Part $part
     * @return FilterRule
     */
    public function setPart(\Application\Model\Part $part)
    {
        $this->part = $part;

        return $this;
    }

    /**
     * Get part
     *
     * @return \Application\Model\Part
     */
    public function getPart()
    {
        return $this->part;
    }

    /**
     * Set justification
     *
     * @param string $justification
     * @return FilterRule
     */
    public function setJustification($justification)
    {
        $this->justification = $justification;

        return $this;
    }

    /**
     * Get justification
     *
     * @return string
     */
    public function getJustification()
    {
        return $this->justification;
    }

}
