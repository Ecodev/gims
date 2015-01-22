<?php

return [
    'controllers' => [
        'invokables' => [
            'Export\Controller\Index' => 'Export\Controller\IndexController',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'router' => [
        'routes' => [
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /export/:controller/:action
            'export' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/export/:action/:id/:filename',
                    'defaults' => [
                        '__NAMESPACE__' => 'Export\Controller',
                        'controller' => 'Index',
                        'action' => 'index',
                    ],
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]*',
                    ],
                ],
                'may_terminate' => true,
            ],
        ],
    ],
    'view_helpers' => [
        'invokables' => [
        ],
    ],
];
