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
        'firewall_controller' => true,
        'firewalls' => array(
            'ZfcRbac\Firewall\Controller' => array(
//                array('controller' => 'Contribute\Controller\Index', 'actions' => 'index', 'roles' => 'member'),
//                array('controller' => 'Admin\Controller\Survey', 'actions' => 'index', 'roles' => 'member'),
            ),
            'ZfcRbac\Firewall\Route' => array(
                // Only members can access admin and contribute angular templates
                array('route' => 'template_admin', 'roles' => 'member'),
                array('route' => 'template_contribute', 'roles' => 'member'),
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
