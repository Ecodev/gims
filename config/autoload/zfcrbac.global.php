<?php

return [
    'service_manager' => [
        'factories' => [
            'ZfcRbac\Service\AuthorizationService' => 'Application\Service\AuthorizationServiceFactory',
            'ZfcRbac\View\Strategy\UnauthorizedStrategy' => 'Application\Service\UnauthorizedStrategyFactory',
        ],
        'aliases' => [
            'Zend\Authentication\AuthenticationService' => 'zfcuser_auth_service',
        ],
    ],
    'zfc_rbac' => [
        'guest_role' => 'anonymous',
        'role_provider' => [
            'ZfcRbac\Role\ObjectRepositoryRoleProvider' => [
                'object_manager' => 'doctrine.entitymanager.orm_default', // alias for doctrine ObjectManager
                'class_name' => 'Application\Model\Role', // FQCN for your role entity class
                'role_name_property' => 'name', // Name to show
            ],
        ],
        'guards' => [
            'ZfcRbac\Guard\RouteGuard' => [
                // Only members can access admin and contribute angular templates
                'template_admin' => ['member'],
                'template_admin/default' => ['member'],
                'template_contribute' => ['member'],
                'template_contribute/default' => ['member'],
                'api/users' => ['member'],
            ],
        ],
    ],
];
