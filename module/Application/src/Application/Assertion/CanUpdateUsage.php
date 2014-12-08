<?php

namespace Application\Assertion;

use ZfcRbac\Service\AuthorizationService;
use Application\Service\RoleContextInterface;
use Application\Utility;

class CanUpdateUsage extends AbstractAssertion
{

    /**
     * @var \Application\Model\Rule\AbstractUsage
     */
    private $usage;

    /**
     * @var \Application\Service\RoleContextInterface
     */
    private $context;

    /**
     *
     * @param \Application\Model\Rule\AbstractUsage $usage
     */
    public function __construct(\Application\Model\Rule\AbstractUsage $usage, RoleContextInterface $context = null)
    {
        $this->usage = $usage;
        $this->context = $context;
    }

    protected function getInternalMessage()
    {
        $questionnaire = Utility::objectsToString([$this->context]);

        return 'Usage cannot be modified because it is used for published questionnaire: ' . $questionnaire;
    }

    protected function internalAssert(AuthorizationService $authorizationService)
    {
        $isPublished = false;

        if ($this->context->getSurvey()->getType() == \Application\Model\SurveyType::$GLAAS && $this->context->getStatus() == \Application\Model\QuestionnaireStatus::$PUBLISHED) {
            $isPublished = true;
        }

        return !$isPublished;
    }

}
