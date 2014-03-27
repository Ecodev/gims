<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * QuestionnaireUsage allows us to "apply" a formula to a questionnaire-part pair. This
 * is used for what is called Calculations, Estimates and Ratios in original Excel files.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\Rule\QuestionnaireUsageRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="questionnaire_usage_unique",columns={"questionnaire_id", "part_id", "rule_id"})})
 */
class QuestionnaireUsage extends AbstractQuestionnaireUsage
{

    /**
     * @inheritdoc
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'rule',
            'questionnaire',
            'part',
        ));
    }

    /**
     * Set questionnaire
     *
     * @param \Application\Model\Questionnaire $questionnaire
     * @return QuestionnaireUsage
     */
    public function setQuestionnaire(\Application\Model\Questionnaire $questionnaire)
    {
        parent::setQuestionnaire($questionnaire);
        $this->getQuestionnaire()->questionnaireUsageAdded($this);

        return $this;
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
