<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * Common properties to "apply" a formula to something, on questionnaire level
 * @ORM\MappedSuperclass
 */
abstract class AbstractQuestionnaireUsage extends AbstractUsage
{

    protected $questionnaire;

    /**
     * Set questionnaire
     *
     * @param \Application\Model\Questionnaire $questionnaire
     * @return QuestionnaireUsage
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
     * Get Filter
     * @return Filter|null
     */
    abstract public function getFilter();

    /**
     * Returns role context
     * @param string $action
     * @return \Application\Model\Questionnaire
     */
    public function getRoleContext($action)
    {
        return $this->getQuestionnaire();
    }
}
