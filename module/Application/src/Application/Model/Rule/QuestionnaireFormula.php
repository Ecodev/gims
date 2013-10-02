<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * QuestionnaireFormula allows us to "apply" a formula to a questionnaire-part pair. This
 * is used for what is called Calculations, Estimates and Ratios in original Excel files.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\Rule\QuestionnaireFormulaRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="questionnaire_formula_unique",columns={"questionnaire_id", "part_id", "formula_id"})})
 */
class QuestionnaireFormula extends AbstractFormulaUsage
{

    /**
     * @var Formula
     *
     * @ORM\ManyToOne(targetEntity="Formula")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $formula;

    /**
     * Set formula
     *
     * @param Formula $formula
     * @return QuestionnaireFormula
     */
    public function setFormula(Formula $formula)
    {
        $this->formula = $formula;

        return $this;
    }

    /**
     * Set questionnaire
     *
     * @param \Application\Model\Questionnaire $questionnaire
     * @return QuestionnaireFormula
     */
    public function setQuestionnaire(\Application\Model\Questionnaire $questionnaire)
    {
        parent::setQuestionnaire($questionnaire);
        $this->getQuestionnaire()->questionnaireFormulaAdded($this);

        return $this;
    }

    /**
     * Get formula
     *
     * @return Formula
     */
    public function getFormula()
    {
        return $this->formula;
    }

    /**
     * Get Filter, always return null
     * @return null
     */
    public function getFilter()
    {
        return null;
    }

}
