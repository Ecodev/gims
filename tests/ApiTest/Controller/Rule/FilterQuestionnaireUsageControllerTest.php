<?php

namespace ApiTest\Controller\Rule;

use ApiTest\Controller\AbstractChildRestfulControllerTest;

/**
 * @group Rest
 */
class FilterQuestionnaireUsageControllerTest extends AbstractChildRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return array('id', 'rule', 'questionnaire', 'part', 'filter', 'justification');
    }

    protected function getTestedObject()
    {
        return $this->filterQuestionnaireUsage;
    }

    protected function getPossibleParents()
    {
        return array(
            $this->filterQuestionnaireUsage->getRule(),
            $this->filterQuestionnaireUsage->getQuestionnaire(),
            $this->filterQuestionnaireUsage->getFilter(),
        );
    }

}
