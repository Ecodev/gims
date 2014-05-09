<?php

namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * This an ugly exact copy/paste of \ZfcRbac\Factory\AuthorizationServiceFactory
 * to allow usage of custom \Application\Service\AuthorizationService
 * @TODO: Replace this ugly copy/paste with configuration if available
 */
class AuthorizationServiceFactory implements FactoryInterface
{

    /**
     * {@inheritDoc}
     * @return AuthorizationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var \Rbac\Rbac $rbac */
        $rbac = $serviceLocator->get('Rbac\Rbac');

        /* @var \ZfcRbac\Service\RoleService $roleService */
        $roleService = $serviceLocator->get('ZfcRbac\Service\RoleService');

        /* @var \ZfcRbac\Assertion\AssertionPluginManager $assertionPluginManager */
        $assertionPluginManager = $serviceLocator->get('ZfcRbac\Assertion\AssertionPluginManager');

        /* @var \ZfcRbac\Options\ModuleOptions $moduleOptions */
        $moduleOptions = $serviceLocator->get('ZfcRbac\Options\ModuleOptions');

        $authorizationService = new AuthorizationService($rbac, $roleService, $assertionPluginManager);
        $authorizationService->setAssertions($moduleOptions->getAssertionMap());

        return $authorizationService;
    }

}
