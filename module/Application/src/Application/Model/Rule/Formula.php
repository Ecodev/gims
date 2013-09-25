<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * Formula is a way to define a value as a custom formula.
 *
 * Syntax is based on Excel formula syntax. Except cell references (eg: A2, B3)
 * must be entirely replaced with following syntax:
 *
 * Reference a Filter's value:
 * {F#12,Q#34,P#56}
 *
 * Reference a Filter's regression value for same part and year. This is only
 * available when doing regression computing, so when used by Calculator\Jmp:
 * {F#12}
 *
 * Reference an Unofficial Filter name (NOT value). It will return NULL if no
 * Unofficial Filter is found. The ID refers to the official Filter:
 * {F#12,Q#34}
 *
 * Reference a QuestionnaireFormula's value:
 * {Fo#12,Q#34,P#56}
 *
 * Reference a population data of the questionnaire's country:
 * {Q#34,P#56}
 *
 * Reference the value if computed without this formula. It allows for formulas
 * chaining:
 * {self}
 *
 * Where:
 * - F  = Filter
 * - Q  = Questionnaire
 * - P  = Part
 * - Fo = Formula
 *
 * In the first case, F, and in all cases Q and P, can have the value "current" instead of actual ID. It means
 * that the current Filter, Questionnaire or Part should be used, instead of one selected
 * by its ID. This syntax should be prefered when possible to maximise Formula re-use.
 *
 * An entire formula could be:
 * <samp>=IF(ISTEXT({F#12,Q#34}), SUM({F#12,Q#34,P#56}, {Fo#2,Q#34,P#56}), {Fo#2,Q#34,P#56})<samp>
 *
 * Or the more re-usable version:
 * <samp>=IF(ISTEXT({F#12,Q#current}), SUM({F#12,Q#current,P#current}, {Fo#2,Q#current,P#current}), {Fo#2,Q#current,P#current})<samp>
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
