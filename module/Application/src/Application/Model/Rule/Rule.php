<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rule is a way to define a value as a custom formula.
 *
 * Syntax is based on Excel formula syntax. Except cell references (eg: A2, B3)
 * must be entirely replaced with following syntax. There is different syntax
 * for the two different context.
 *
 * Basic context (Calculator\Calculator):
 * <ul>
 *     <li>
 *         Reference a Filter's value:
 *         <pre>{F#12,Q#34,P#56}</pre>
 *     </li>
 *     <li>
 *         Reference an Unofficial Filter name (NOT value). It will return NULL if no
 *         Unofficial Filter is found. The ID refers to the official Filter:
 *         <pre>{F#12,Q#34}</pre>
 *     </li>
 *     <li>
 *         Reference a QuestionnaireUsage's value:
 *         <pre>{R#12,Q#34,P#56}</pre>
 *     </li>
 *     <li>
 *         Reference a population data of the questionnaire's country:
 *         <pre>{Q#34,P#56}</pre>
 *     </li>
 * </ul>
 *
 * Regression context (Calculator\Jmp):
 * <ul>
 *     <li>
 *         Reference a Filter's regression value for same part and year:
 *         <pre>{F#12}</pre>
 *     </li>
 *     <li>
 *         Reference a list of available filter values for all current questionnaires.
 *         The result use Excel array constant syntax (eg: "{1,2,3,4,5}")
 *         <pre>{F#12,Q#all}</pre>
 *     </li>
 * </ul>
 *
 * Both context:
 * <ul>
 *     <li>
 *         Reference the value if computed without this formula. It allows
 *         for formulas chaining:
 *         <pre>{self}</pre>
 *     </li>
 * </ul>
 *
 * Where:
 * - F = Filter
 * - Q = Questionnaire
 * - P = Part
 * - R = Rule
 *
 * In the first case, F, and in all cases Q and P, can have the value "current" instead of actual ID. It means
 * that the current Filter, Questionnaire or Part should be used, instead of one selected
 * by its ID. This syntax should be prefered when possible to maximise Rule re-use.
 *
 * An entire formula could be:
 * <pre>=IF(ISTEXT({F#12,Q#34}), SUM({F#12,Q#34,P#56}, {R#2,Q#34,P#56}), {R#2,Q#34,P#56})</pre>
 *
 * Or the more re-usable version:
 * <pre>=IF(ISTEXT({F#12,Q#current}), SUM({F#12,Q#current,P#current}, {R#2,Q#current,P#current}), {R#2,Q#current,P#current})</pre>
 *
 * @ORM\Entity(repositoryClass="Application\Repository\Rule\RuleRepository")
 */
class Rule extends \Application\Model\AbstractModel
{

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, length=4096)
     */
    private $formula;

    /**
     * Set name
     *
     * @param string $name
     * @return Rule
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
