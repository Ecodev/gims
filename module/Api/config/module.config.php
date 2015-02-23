<?php

return [
    'controllers' => [
        'invokables' => [
            'Api\Controller\QuestionType' => \Api\Controller\QuestionTypeController::class,
            'Api\Controller\Choice' => \Api\Controller\ChoiceController::class,
            'Api\Controller\Questionnaire' => \Api\Controller\QuestionnaireController::class,
            'Api\Controller\Question' => \Api\Controller\QuestionController::class,
            'Api\Controller\Answer' => \Api\Controller\AnswerController::class,
            'Api\Controller\Filter' => \Api\Controller\FilterController::class,
            'Api\Controller\Survey' => \Api\Controller\SurveyController::class,
            'Api\Controller\FilterSet' => \Api\Controller\FilterSetController::class,
            'Api\Controller\Part' => \Api\Controller\PartController::class,
            'Api\Controller\User' => \Api\Controller\UserController::class,
            'Api\Controller\UserSurvey' => \Api\Controller\UserSurveyController::class,
            'Api\Controller\UserQuestionnaire' => \Api\Controller\UserQuestionnaireController::class,
            'Api\Controller\UserFilterSet' => \Api\Controller\UserFilterSetController::class,
            'Api\Controller\Role' => \Api\Controller\RoleController::class,
            'Api\Controller\Geoname' => \Api\Controller\GeonameController::class,
            'Api\Controller\Chart' => \Api\Controller\ChartController::class,
            'Api\Controller\Table' => \Api\Controller\TableController::class,
            'Api\Controller\Comment' => \Api\Controller\CommentController::class,
            'Api\Controller\Discussion' => \Api\Controller\DiscussionController::class,
            'Api\Controller\Rule' => \Api\Controller\Rule\RuleController::class,
            'Api\Controller\Population' => \Api\Controller\PopulationController::class,
            'Api\Controller\QuestionnaireUsage' => \Api\Controller\Rule\QuestionnaireUsageController::class,
            'Api\Controller\FilterQuestionnaireUsage' => \Api\Controller\Rule\FilterQuestionnaireUsageController::class,
            'Api\Controller\FilterGeonameUsage' => \Api\Controller\Rule\FilterGeonameUsageController::class,
            'Api\Controller\Children' => \Api\Controller\FilterController::class,
            'Api\Controller\Filters' => \Api\Controller\FilterController::class,
            'Api\Controller\Activity' => \Api\Controller\ActivityController::class,
            'Api\Controller\RolesRequest' => \Api\Controller\RolesRequestController::class,
        ],
    ],
    'router' => [
        'routes' => [
            'api' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/api',
                    'defaults' => [
                        '__NAMESPACE__' => 'Api\Controller',
                    ],
                ],
                'child_routes' => [
                    // The following is a route to simplify getting started creating
                    // new controllers and actions without needing to create a new
                    // module. Simply drop new controllers in, and you can access them
                    // using the path /api/:controller/:id
                    'default' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/[:controller[/:id]]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]+',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'Api\Controller',
                            ],
                        ],
                    ],
                    // This route allow to execute non-restfull actions on controllers
                    'controller_actions' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/:controller/:action',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'Api\Controller',
                            ],
                        ],
                    ],
                    // This route allow to ask for subobjects of an object
                    'subobject' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/:parent/:idParent/:controller[/:id]',
                            'constraints' => [
                                'parent' => '(chapter|user|survey|role|questionnaire|filterSet|rule|filter|geoname)',
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'idParent' => '[0-9]+',
                                'id' => '[0-9]+',
                            ],
                            'defaults' => [],
                        ],
                    ],
                    // This route allow to ask for sub-sub-subobjects of an object
                    'subsubsubobject' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/:parent1/:idParent1/:parent2/:idParent2/:parent3/:idParent3/:controller[/:id]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'parent1' => '(questionnaire)',
                                'parent2' => '(filter)',
                                'parent3' => '(part)',
                                'idParent1' => '[0-9]+',
                                'idParent2' => '[0-9]+',
                                'idParent3' => '[0-9]+',
                                'id' => '[0-9]+',
                            ],
                            'defaults' => [],
                        ],
                    ],
                    // This route is same as default, but only for users
                    // Creating a specific rule dedicated to /api/users  allow to restrict access to members only
                    // see /config/autoload/zfcrbac.global.php
                    'users' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/user',
                            'constraints' => [],
                            'defaults' => [
                                '__NAMESPACE__' => 'Api\Controller',
                                'controller' => 'user',
                            ],
                        ],
                    ],
                    // This route allow to execute something on a user (eg:computing stats)
                    'user_actions' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/user/:idUser/[:action]',
                            'constraints' => [
                                'action' => '(statistics)', // Define here allowed actions: (action1|action2|action3)
                                'idUser' => '[0-9]+',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'Api\Controller',
                                'controller' => 'user',
                            ],
                        ],
                    ],
                    // This route allow to call a non REST controller with action
                    'non_rest_controller' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/:controller[/:action][/:filename]',
                            'constraints' => [
                                'controller' => '(chart|table)', // Define here allowed controllers: (controller1|controller2|controller3)
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'Api\Controller',
                                'action' => 'index',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'strategies' => [
            'ViewExcelStrategy',
            'ViewJsonStrategy',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
