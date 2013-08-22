<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rule is a way to exclude specify custom behavior during computing of filter.
 * This is on a filter-questionnaire-part basis.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\RuleRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\Table(name="rule")
 */
abstract class AbstractRule extends \Application\Model\AbstractModel
{

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isFinal = false;

    /**
     * Set name
     *
     * @param string $name
     * @return AbstractRule
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Return the name of this rule (for end-user)
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Indicate that we must NOT sum the subfilters to the rule value (default false)
     *
     * @param boolean $value
     */
    public function setIsFinal($value)
    {
        $this->isFinal = (bool) $value;

        return $this;
    }

    /**
     * Indicate that we must NOT sum the subfilters to the rule value (default false)
     *
     * @return boolean
     */
     public function isFinal()
     {
        return $this->isFinal;
     }
}