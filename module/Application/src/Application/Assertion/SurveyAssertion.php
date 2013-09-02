<?php

namespace Application\Assertion;

use ZfcRbac\Service\Rbac;

class SurveyAssertion implements \ZfcRbac\Assertion\AssertionInterface
{
    /**
     * @var \Application\Model\Survey
     */
    protected $survey;

    /**
     * @param \Application\Model\Survey $survey
     * @return \Application\Assertion\SurveyAssertion
     */
    public function __construct($survey){
        $this->survey = $survey;
    }

    /**
     * Dynamic assertion.
     *
     * @param \ZfcRbac\Service\Rbac $rbac
     *
     * @return boolean
     */
    public function assert(Rbac $rbac)
    {
        // @todo define the rules and implement me!
        return true;
    }
}
