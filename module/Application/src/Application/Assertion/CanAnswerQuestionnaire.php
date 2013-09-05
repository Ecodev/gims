<?php

namespace Application\Assertion;

use ZfcRbac\Service\Rbac;

class CanAnswerQuestionnaire extends AbstractAssertion
{

    /**
     * @var \Application\Model\Answer
     */
    protected $answer;

    /**
     *
     * @param \Application\Model\Answer $answer
     */
    public function __construct(\Application\Model\Answer $answer)
    {
        $this->answer = $answer;
    }

    protected function getInternalMessage()
    {
        return 'Answers cannot be modified when questionnaire is marked as ' . \Application\Model\QuestionnaireStatus::$VALIDATED;
    }

    protected function internalAssert(Rbac $rbac)
    {
        return $this->answer->getQuestionnaire()->getStatus() != \Application\Model\QuestionnaireStatus::$VALIDATED;
    }

}
