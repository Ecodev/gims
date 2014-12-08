<?php

namespace Application\Assertion;

use ZfcRbac\Service\AuthorizationService;
use Application\Model\SurveyType;

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
        $statusForbiddingModification = [];
        $statusForbiddingModification[(string) SurveyType::$GLAAS . ''] = [
            \Application\Model\QuestionnaireStatus::$VALIDATED,
            \Application\Model\QuestionnaireStatus::$PUBLISHED
        ];

        $surveyType = (string) $this->answer->getQuestionnaire()->getSurvey()->getType();
        $searchArray = isset($statusForbiddingModification[$surveyType]) ? $statusForbiddingModification[$surveyType] : [];

        return !in_array($this->answer->getQuestionnaire()->getStatus(), $searchArray);
    }

}
