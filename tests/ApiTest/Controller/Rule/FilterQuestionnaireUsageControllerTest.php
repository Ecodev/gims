<?php

namespace ApiTest\Controller\Rule;

/**
 * @group Rest
 */
class FilterQuestionnaireUsageControllerTest extends AbstractQuestionnaireUsageControllerTest
{

    protected function getAllowedFields()
    {
        return [
            'id',
            'rule',
            'questionnaire',
            'part',
            'filter',
            'justification'
        ];
    }

    protected function getTestedObject()
    {
        return $this->filterQuestionnaireUsage;
    }

    protected function getPossibleParents()
    {
        return [
            $this->filterQuestionnaireUsage->getRule(),
            $this->filterQuestionnaireUsage->getQuestionnaire(),
            $this->filterQuestionnaireUsage->getFilter(),
        ];
    }

}
