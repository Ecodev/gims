<?php

namespace Application\Assertion;

use ZfcRbac\Service\AuthorizationService;
use Application\Service\RoleContextInterface;
use Application\Utility;

class CanUpdateRule extends AbstractAssertion
{

    /**
     * @var \Application\Model\Rule\Rule
     */
    private $rule;

    /**
     * @var \Application\Service\RoleContextInterface
     */
    private $context;

    /**
     * @var array
     */
    private $publishedQuestionnaires = [];

    /**
     *
     * @param \Application\Model\Rule\Rule $rule
     */
    public function __construct(\Application\Model\Rule\Rule $rule, RoleContextInterface $context = null)
    {
        $this->rule = $rule;
        $this->context = $context;
    }

    protected function getInternalMessage()
    {
        $questionnaires = Utility::objectsToString($this->publishedQuestionnaires);

        return 'Rule cannot be modified because it is used for published questionnaires: ' . $questionnaires;
    }

    protected function internalAssert(AuthorizationService $authorizationService)
    {
        if ($this->context instanceof \Application\Service\MultipleRoleContext) {
            foreach ($this->context as $questionnaire) {
                if ($questionnaire->getStatus() == \Application\Model\QuestionnaireStatus::$PUBLISHED) {
                    $this->publishedQuestionnaires[] = $questionnaire;
                }
            }
        }

        return !$this->publishedQuestionnaires;
    }

}
