<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Browse\Controller\Index' => 'Browse\Controller\IndexController',
            'Browse\Controller\Chart' => 'Browse\Controller\ChartController',
            'Browse\Controller\Table' => 'Browse\Controller\TableController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'router' => array(
        'routes' => array(
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /browse/:controller/:action
            'template_browse' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/template/browse',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Browse\Controller',
                        'controller' => 'Index',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '[/:controller][/:action]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                'action' => 'index',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
