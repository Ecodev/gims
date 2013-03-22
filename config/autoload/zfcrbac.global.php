<?php

return array(
    'service_manager' => array(
        'factories' => array(
            'ZfcRbac\Service\Rbac' => 'Application\Service\RbacFactory',
        ),
    ),
    'zfcrbac' => array(
        'firewalls' => array(
            'ZfcRbac\Firewall\Controller' => array(
                array('controller' => 'index', 'actions' => 'index', 'roles' => 'guest')
            ),
            'ZfcRbac\Firewall\Route' => array(
                array('route' => 'profiles/add', 'roles' => 'member'),
                array('route' => 'admin/*', 'roles' => 'administrator')
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