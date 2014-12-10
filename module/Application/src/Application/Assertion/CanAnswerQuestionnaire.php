<?php

namespace Application\Assertion;

use ZfcRbac\Service\AuthorizationService;

class CanAnswerQuestionnaire extends AbstractAssertion
{

    /**
     * @var \Application\Model\Answer
     */
    private $answer;

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
        return 'Answers cannot be modified when questionnaire is marked as ' . $this->answer->getQuestionnaire()->getStatus();
    }

    protected function internalAssert(AuthorizationService $authorizationService)
    {
        $statusForbiddingModification = [
            \Application\Model\QuestionnaireStatus::$VALIDATED,
            \Application\Model\QuestionnaireStatus::$PUBLISHED,
        ];

        return !in_array($this->answer->getQuestionnaire()->getStatus(), $statusForbiddingModification);
    }

}
