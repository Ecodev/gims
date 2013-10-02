<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * Common properties to "apply" a formula to something, on questionnaire level
 * @ORM\MappedSuperclass
 */
abstract class AbstractFormulaUsage extends AbstractRuleUsage
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
     * Set questionnaire
     *
     * @param \Application\Model\Questionnaire $questionnaire
     * @return QuestionnaireFormula
     */
    public function setQuestionnaire(\Application\Model\Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;

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
     * Get Formula
     * @return Formula|null
     */
    abstract function getFormula();

    /**
     * Get Filter
     * @return Filter|null
     */
    abstract function getFilter();
}
