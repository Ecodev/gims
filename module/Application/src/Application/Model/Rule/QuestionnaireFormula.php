<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * QuestionnaireFormula allows us to "apply" a formula to a questionnaire-part pair. This
 * is used for what is called Calculations, Estimates and Ratios in original Excel files.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\QuestionnaireFormulaRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="questionnaire_formula_unique",columns={"questionnaire_id", "part_id", "formula_id"})})
 */
class QuestionnaireFormula extends \Application\Model\AbstractModel
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
     * @var Part
     *
     * @ORM\ManyToOne(targetEntity="Application\Model\Part")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $part;

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
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $justification;

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
     * Set part
     *
     * @param \Application\Model\Part $part
     * @return QuestionnaireFormula
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

    /**
     * Set justification
     *
     * @param string $justification
     * @return QuestionnaireFormula
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
