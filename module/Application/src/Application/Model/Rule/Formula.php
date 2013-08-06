<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * Formula is a way to define a value as a custom formula.
 *
 * TODO: For now the formula is not actually implemented but will be
 * in the future, see following tickets:
 * - https://support.ecodev.ch/issues/2073
 * - https://support.ecodev.ch/issues/2074
 *
 * @ORM\Entity
 */
class Formula extends AbstractRule
{

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=1024)
     */
    private $formula;

    /**
     * This is the "cached" result of the formula
     * TODO: it should be removed when real formula engine is implemented. See class header comment
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $value;

    /**
     * Set formula
     *
     * @param string $formula
     * @return Formula
     */
    public function setFormula($formula)
    {
        $this->formula = $formula;

        return $this;
    }

    /**
     * Get formula
     *
     * @return string
     */
    public function getFormula()
    {
        return $this->formula;
    }

    /**
     * Set absolute value
     *
     * @param float $value
     * @return Formula
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get absolute value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

}
