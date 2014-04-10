<?php

return array(
    'service_manager' => array(
        'factories' => array(
            'ZfcRbac\Service\Rbac' => 'Application\Service\RbacFactory',
            'ZfcRbac\View\UnauthorizedStrategy' => 'Application\Service\UnauthorizedStrategyFactory',
        ),
    ),
    'zfcrbac' => array(
        'firewall_route' => true,
        'firewall_controller' => false, // default to true
        'firewalls' => array(
            'ZfcRbac\Firewall\Route' => array(
                // Only members can access admin and contribute angular templates
                array('route' => 'template_admin', 'roles' => 'member'),
                array('route' => 'template_contribute', 'roles' => 'member'),
                array('route' => 'api/users', 'roles' => 'member'),
            ),
        ),
        'providers' => array(
            'ZfcRbac\Provider\AdjacencyList\Role\DoctrineDbal' => array(
                'connection' => 'doctrine.connection.orm_default',
                'options' => array(
                    'join_column' => 'parent_id',
                ),
            ),
            'ZfcRbac\Provider\Generic\Permission\DoctrineDbal' => array(
                'connection' => 'doctrine.connection.orm_default',
            ),
        ),
        'identity_provider' => 'zfcuser_auth_service',
    ),
);
