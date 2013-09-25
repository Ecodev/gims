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
class QuestionnaireFormula extends AbstractRelation
{

    /**
     * @var Questionnaire
     *
     * @ORM\ManyToOne(targetEntity="Application\Model\Questionnaire", inversedBy="questionnaireFormulas"))
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $questionnaire;

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
     * Set questionnaire
     *
     * @param \Application\Model\Questionnaire $questionnaire
     * @return QuestionnaireFormula
     */
    public function setQuestionnaire(\Application\Model\Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;
        $this->questionnaire->questionnaireFormulaAdded($this);

        return $this;
    }

    /**
     * Get questionnaire
     *
     * @return \Application\Model\Questionnaire
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

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
     * Get formula
     *
     * @return Formula
     */
    public function getFormula()
    {
        return $this->formula;
    }

}
