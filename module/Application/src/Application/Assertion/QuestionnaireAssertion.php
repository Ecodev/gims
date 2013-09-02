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
     * @var \ZfcRbac\Service\Rbac
     */
    protected $rbac;

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

    /**
     * Tell whether a questionnaire can be marked as completed.
     *
     * @return bool
     */
    public function canBeCompleted()
    {
        // Get roles
        $roles = $this->rbac->getIdentity()->getRoles();

        // if the user has role reporter
        $assertion = false;
        if (in_array('reporter', $roles)
            && (string) $this->questionnaire->getStatus() !== \Application\Model\QuestionnaireStatus::$VALIDATED
        ) {
            $assertion = true;
        }

        return $assertion;
    }

    /**
     * Tell whether a questionnaire can be marked as validated.
     *
     * @return bool
     */
    public function canBeValidated()
    {
        $roles = $this->rbac->getIdentity()->getRoles();

        // if user has role validate
        // if questionnaire was marked as completed
        $assertion = false;
        if (in_array('validator', $roles)) {
            $assertion = true;
        }

        return $assertion;
    }

    /**
     * Tell whether a questionnaire can be deleted
     *
     * @return bool
     */
    public function canBeDeleted()
    {
        // @todo remove me when permission will be handled
        return true;
        // if the questionnaire has not answer
        return $this->questionnaire->getAnswers()->isEmpty();
    }

    /**
     * @param \ZfcRbac\Service\Rbac $rbac
     */
    public function setRbac($rbac)
    {
        $this->rbac = $rbac;
    }
}
