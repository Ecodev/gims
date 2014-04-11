<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Api\Controller\QuestionType' => 'Api\Controller\QuestionTypeController',
            'Api\Controller\Choice' => 'Api\Controller\ChoiceController',
            'Api\Controller\Questionnaire' => 'Api\Controller\QuestionnaireController',
            'Api\Controller\Question' => 'Api\Controller\QuestionController',
            'Api\Controller\Answer' => 'Api\Controller\AnswerController',
            'Api\Controller\Filter' => 'Api\Controller\FilterController',
            'Api\Controller\Survey' => 'Api\Controller\SurveyController',
            'Api\Controller\FilterSet' => 'Api\Controller\FilterSetController',
            'Api\Controller\Country' => 'Api\Controller\CountryController',
            'Api\Controller\Part' => 'Api\Controller\PartController',
            'Api\Controller\User' => 'Api\Controller\UserController',
            'Api\Controller\UserSurvey' => 'Api\Controller\UserSurveyController',
            'Api\Controller\UserQuestionnaire' => 'Api\Controller\UserQuestionnaireController',
            'Api\Controller\UserFilterSet' => 'Api\Controller\UserFilterSetController',
            'Api\Controller\Role' => 'Api\Controller\RoleController',
            'Api\Controller\Geoname' => 'Api\Controller\GeonameController',
            'Api\Controller\Chart' => 'Api\Controller\ChartController',
            'Api\Controller\Table' => 'Api\Controller\TableController',
            'Api\Controller\Note' => 'Api\Controller\NoteController',
            'Api\Controller\Rule' => 'Api\Controller\RuleController',
            'Api\Controller\QuestionnaireUsage' => 'Api\Controller\Rule\QuestionnaireUsageController',
            'Api\Controller\FilterQuestionnaireUsage' => 'Api\Controller\Rule\FilterQuestionnaireUsageController',
            'Api\Controller\FilterGeonameUsage' => 'Api\Controller\Rule\FilterGeonameUsageController',
            'Api\Controller\children' => 'Api\Controller\FilterController',
            'Api\Controller\filters' => 'Api\Controller\FilterController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'api' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/api',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Api\Controller',
                    ),
                ),
                'child_routes' => array(
                    // The following is a route to simplify getting started creating
                    // new controllers and actions without needing to create a new
                    // module. Simply drop new controllers in, and you can access them
                    // using the path /api/:controller/:id
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:id]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Api\Controller',
                            ),
                        ),
                    ),
                    // This route allow to execute non-restfull actions on controllers
                    'controller_actions' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/:controller/:action',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Api\Controller',
                            ),
                        ),
                    ),
                    // This route allow to ask for subobjects of an object
                    'subobject' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/:parent/:idParent/:controller[/:id]',
                            'constraints' => array(
                                'parent' => '(chapter|user|survey|role|questionnaire|filterSet|rule|filter|geoname)',
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'idParent' => '[0-9]+',
                                'id' => '[0-9]+',
                            ),
                            'defaults' => array(),
                        ),
                    ),
                    // This route is same as default, but only for users
                    // Creating a specific rule dedicated to /api/users  allow to restrict access to members only
                    // see /config/autoload/zfcrbac.global.php
                    'users' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/user',
                            'constraints' => array(),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Api\Controller',
                                'controller' => 'user',
                            ),
                        ),
                    ),
                    // This route allow to execute something on a user (eg:computing stats)
                    'user_actions' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/user/:idUser/[:action]',
                            'constraints' => array(
                                'action' => '(statistics)', // Define here allowed actions: (action1|action2|action3)
                                'idUser' => '[0-9]+',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Api\Controller',
                                'controller' => 'user',
                            ),
                        ),
                    ),
                    // This route allow to call a non REST controller with action
                    'non_rest_controller' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/:controller[/:action][/:filename]',
                            'constraints' => array(
                                'controller' => '(chartFilterGenerator|chartFilter|chart|table)', // Define here allowed controllers: (controller1|controller2|controller3)
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Api\Controller',
                                'action' => 'index',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'strategies' => array(
            'ViewExcelStrategy',
            'ViewJsonStrategy',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
