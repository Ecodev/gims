<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Contribute\Controller\Index' => 'Contribute\Controller\IndexController',
            'Contribute\Controller\RequestRoles' => 'Contribute\Controller\RequestRolesController',
            'Contribute\Controller\Discussion' => 'Contribute\Controller\DiscussionController',
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
            // using the path /contribute/:controller/:action
            'template_contribute' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/template/contribute',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Contribute\Controller',
                        'controller' => 'Index',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
