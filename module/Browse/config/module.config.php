<?php

return [
    'controllers' => [
        'invokables' => [
            'Browse\Controller\Index' => 'Browse\Controller\IndexController',
            'Browse\Controller\Chart' => 'Browse\Controller\ChartController',
            'Browse\Controller\Table' => 'Browse\Controller\TableController',
            'Browse\Controller\Country' => 'Browse\Controller\TableController',
            'Browse\Controller\Rule' => 'Browse\Controller\RuleController',
            'Browse\Controller\CellMenu' => 'Browse\Controller\CellMenuController',
            'Browse\Controller\Discussion' => 'Browse\Controller\DiscussionController',
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'router' => [
        'routes' => [
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /browse/:controller/:action
            'template_browse' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/template/browse',
                    'defaults' => [
                        '__NAMESPACE__' => 'Browse\Controller',
                        'controller' => 'Index',
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'default' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '[/:controller][/:action]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
