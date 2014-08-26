<?php

namespace ApiTest\Controller\Rule;

use Zend\Http\Request;

/**
 * @group Rest
 */
class FilterQuestionnaireUsageControllerTest extends AbstractQuestionnaireUsageControllerTest
{

    protected function getAllowedFields()
    {
        return array(
            'id',
            'rule',
            'questionnaire',
            'part',
            'filter',
            'justification'
        );
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
