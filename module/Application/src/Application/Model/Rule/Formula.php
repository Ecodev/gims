<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * Formula is a way to define a value as a custom formula.
 *
 * Syntax is as follow:
 *
 * Reference a Filter's value:
 * {F#12,Q#34,P#56}
 *
 * Reference an Unofficial Filter name (NOT value). It will return NULL if no Unofficial Filter is found. The ID refers to the official Filter:
 * {F#12,Q#34}
 *
 * Reference a QuestionnaireFormula's value:
 * {Fo#12,Q#34,P#56}
 *
 * Where:
 * - F  = Filter
 * - Q  = Questionnaire
 * - P  = Part
 * - Fo  = Formula
 *
 * In all cases Q and P can have the value "current" instead of actual ID. It means
 * that the current Questionnaire or Part should be used, instead of one selected
 * by its ID. This syntax should prefered when possible to maximise Formula re-use.
 *
 * @ORM\Entity
 */
class Formula extends AbstractRule
{

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=4096)
     */
    private $formula;

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
}
