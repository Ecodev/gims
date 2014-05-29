<?php

namespace ApiTest\Controller\Rule;

/**
 * @group Rest
 */
class QuestionnaireUsageControllerTest extends AbstractUsageControllerTest
{

    protected function getAllowedFields()
    {
        return array('id', 'rule', 'questionnaire', 'part', 'justification');
    }

    protected function getTestedObject()
    {
        return $this->questionnaireUsage;
    }

    protected function getPossibleParents()
    {
        return array(
            $this->questionnaireUsage->getRule(),
            $this->questionnaireUsage->getQuestionnaire(),
        );
    }

}
