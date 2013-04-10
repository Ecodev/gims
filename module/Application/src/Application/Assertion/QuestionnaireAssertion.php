<?php

namespace Application\Assertion;

use ZfcRbac\Service\Rbac;

class QuestionnaireAssertion implements \ZfcRbac\Assertion\AssertionInterface
{
    /**
     * @var \Application\Model\Questionnaire
     */
    protected $questionnaire;

    /**
     * @param \Application\Model\Questionnaire $questionnaire
     * @return \Application\Assertion\QuestionnaireAssertion
     */
    public function __construct($questionnaire){
        $this->questionnaire = $questionnaire;
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
        // @todo make sure the questionnaire is not already validated
        // pseud code:
        // $validate = true; foreach ($questionnaire->getanswers) { $validdate &= $answer->getStatus() == VALIDATED}
        return true;
    }
}