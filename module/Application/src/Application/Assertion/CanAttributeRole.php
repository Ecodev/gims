<?php

namespace Application\Assertion;

use ZfcRbac\Service\AuthorizationService;

class CanAttributeRole extends AbstractAssertion
{

    /**
     * @var \Application\Model\Role
     */
    private $userRole;

    /**
     *
     * @param \Application\Model\AbstractUserRole $userRole
     */
    public function __construct(\Application\Model\AbstractUserRole $userRole)
    {
        $this->userRole = $userRole;
    }

    protected function getInternalMessage()
    {
        return 'A user cannot give permission to publish if he himself does not have it';
    }

    protected function internalAssert(AuthorizationService $authorizationService)
    {
        $sensiblePermission = 'Questionnaire-publish';
        $authorizationService->getIdentity()->resetRolesContext();
        if ($this->userRole->getRole()->hasPermission($sensiblePermission)) {
            return $authorizationService->isGranted($sensiblePermission);
        }

        return true;
    }
}
