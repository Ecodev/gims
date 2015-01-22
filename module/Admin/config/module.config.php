<?php

return [
    'controllers' => [
        'invokables' => [
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            'Admin\Controller\FilterSet' => 'Admin\Controller\FilterSetController',
            'Admin\Controller\Filter' => 'Admin\Controller\FilterController',
            'Admin\Controller\Question' => 'Admin\Controller\QuestionController',
            'Admin\Controller\Questionnaire' => 'Admin\Controller\QuestionnaireController',
            'Admin\Controller\Survey' => 'Admin\Controller\SurveyController',
            'Admin\Controller\User' => 'Admin\Controller\UserController',
            'Admin\Controller\Rule' => 'Admin\Controller\RuleController',
            'Admin\Controller\RolesRequests' => 'Admin\Controller\RolesRequestsController',
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
            // using the path /admin/:controller/:action
            'template_admin' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/template/admin',
                    'defaults' => [
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller' => 'Index',
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'default' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/[:controller[/:action]]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                            #'__NAMESPACE__' => 'Admin\Controller',
                            #'controller'    => 'Survey',
                            #'action'        => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
