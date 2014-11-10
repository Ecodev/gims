<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            'Admin\Controller\FilterSet' => 'Admin\Controller\FilterSetController',
            'Admin\Controller\Filter' => 'Admin\Controller\FilterController',
            'Admin\Controller\Question' => 'Admin\Controller\QuestionController',
            'Admin\Controller\Questionnaire' => 'Admin\Controller\QuestionnaireController',
            'Admin\Controller\Survey' => 'Admin\Controller\SurveyController',
            'Admin\Controller\User' => 'Admin\Controller\UserController',
            'Admin\Controller\Rule' => 'Admin\Controller\RuleController',
            'Admin\Controller\RolesRequests' => 'Admin\Controller\RolesRequestsController',
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
            // using the path /admin/:controller/:action
            'template_admin' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/template/admin',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Admin\Controller',
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
                            'defaults' => array(
                            #'__NAMESPACE__' => 'Admin\Controller',
                            #'controller'    => 'Survey',
                            #'action'        => 'index',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
);
