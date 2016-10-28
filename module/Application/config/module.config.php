<?php

return [
    'Calculator\Cache' => [
        'enabled' => true,
        'namespace' => 'gims',
        'host' => 'localhost',
        'password' => null,
    ],
    'compressJavaScript' => true,
    'bodyCssClass' => null,
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
                'params' => [
                    'host' => 'localhost',
                    'user' => 'postgres',
                    'dbname' => 'gims',
                    'port' => 5432,
                ],
                'doctrine_type_mappings' => [
                    'geometry' => 'geometry',
                    'point' => 'point',
                    'polygon' => 'polygon',
                    'linestring' => 'linestring',
                    'questionnaire_status' => 'questionnaire_status',
                    'survey_type' => 'survey_type',
                ],
            ],
        ],
        'driver' => [
            'application_entities' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/Application/Model'],
            ],
            'orm_default' => [
                'drivers' => [
                    'Application\Model' => 'application_entities',
                ],
            ],
        ],
        'configuration' => [
            'orm_default' => [
                'naming_strategy' => \Application\Service\NamingStrategyFactory::class,
                'types' => [
                    'geometry' => 'CrEOF\Spatial\DBAL\Types\GeometryType',
                    'point' => 'CrEOF\Spatial\DBAL\Types\Geometry\PointType',
                    'polygon' => 'CrEOF\Spatial\DBAL\Types\Geometry\PolygonType',
                    'linestring' => 'CrEOF\Spatial\DBAL\Types\Geometry\LineStringType',
                    'questionnaire_status' => \Application\DBAL\Types\QuestionnaireStatusType::class,
                    'survey_type' => \Application\DBAL\Types\SurveyTypeType::class,
                ],
                'proxy_dir' => 'data/cache/DoctrineORMModule/Proxy',
                'generate_proxies' => false,
                'datetime_functions' => [
                    'CAST' => \Application\ORM\AST\Functions\CastFunction::class,
                ],
                'string_functions' => [
                    'CAST' => \Application\ORM\AST\Functions\CastFunction::class,
                ],
                'numeric_functions' => [
                    'CAST' => \Application\ORM\AST\Functions\CastFunction::class,
                ],
            ],
        ],
        // migrations configuration
        'migrations_configuration' => [
            'orm_default' => [
                'directory' => 'data/migrations',
                'name' => 'GIMS Migrations',
                'namespace' => 'DoctrineMigrations',
                'table' => 'version',
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'home' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'controller' => \Application\Controller\Index::class,
                        'action' => 'index',
                    ],
                ],
            ],
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            //
            // It will never be matched for incoming URL, but it will be used to assemble URL
            'application' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/application',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
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
                            ],
                        ],
                    ],
                ],
            ],
            // Anything which is not API, export or template are redirected to Application\Controller\Index::indexAction()
            'angularjs_layout' => [
                'type' => 'Regex',
                'options' => [
                    'regex' => '^(?!(/api|/export|/template|/ocra_service_manager_yuml))(?<anything>.*)',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Index',
                        'action' => 'index',
                    ],
                    'spec' => '/%anything%',
                ],
            ],
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'template_application' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/template/application',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
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
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
            'ViewExcelRenderer' => \Application\Service\ViewExcelRendererFactory::class,
            'ViewExcelStrategy' => \Application\Service\ViewExcelStrategyFactory::class,
            \Application\Service\NamingStrategyFactory::class => \Application\Service\NamingStrategyFactory::class,
            'Calculator\Cache' => \Application\Service\Calculator\Cache\Factory::class,
        ],
    ],
    'translator' => [
        'locale' => 'en',
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            'Application\Controller\Index' => \Application\Controller\IndexController::class,
            'Application\Controller\Console' => \Application\Controller\ConsoleController::class,
            'Application\Controller\Email' => \Application\Controller\EmailController::class,
        ],
        'factories' => [
            'zfcuser' => \Application\Controller\AuthControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => [
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'headLink' => \Application\View\Helper\HeadLink::class,
            'headScript' => \Application\View\Helper\HeadScript::class,
            'metadata' => \Application\View\Helper\Metadata::class,
            'crudButtons' => \Application\View\Helper\CrudButtons::class,
            'bodyCssClass' => \Application\View\Helper\BodyCssClass::class,
            'version' => \Application\View\Helper\Version::class,
            'excelTable' => \Application\View\Helper\ExcelTable::class,
            'googleAnalytics' => \Application\View\Helper\GoogleAnalytics::class,
            'helpButton' => \Application\View\Helper\HelpButton::class,
            'helpBox' => \Application\View\Helper\HelpBox::class,
        ],
    ],
    'console' => [
        'router' => [
            'routes' => [
                'import-jmp' => [
                    'description' => 'Import individual country file in JMP format',
                    'options' => [
                        'route' => 'import jmp <file>',
                        'defaults' => [
                            'controller' => \Application\Controller\Console::class,
                            'action' => 'importJmp',
                        ],
                    ],
                ],
                'import-glass' => [
                    'description' => 'Import filters for GLASS and affect them to existing question',
                    'options' => [
                        'route' => 'import glass',
                        'defaults' => [
                            'controller' => \Application\Controller\Console::class,
                            'action' => 'importGlass',
                        ],
                    ],
                ],
                'import-population' => [
                    'description' => 'Import population data',
                    'options' => [
                        'route' => 'import population <file>',
                        'defaults' => [
                            'controller' => \Application\Controller\Console::class,
                            'action' => 'importPopulation',
                        ],
                    ],
                ],
                'email' => [
                    'description' => 'Send email',
                    'options' => [
                        'route' => 'email <action> <id>',
                        'defaults' => [
                            'controller' => \Application\Controller\Email::class,
                        ],
                    ],
                ],
                'email-role-request' => [
                    'description' => 'Send email for role request',
                    'options' => [
                        'route' => 'email notifyRoleRequest <recipientsIds> <applicantUserId> <emailLinkQueryString>',
                        'defaults' => [
                            'controller' => \Application\Controller\Email::class,
                            'action' => 'notifyRoleRequest',
                        ],
                    ],
                ],
                'generate-welcome-email' => [
                    'description' => 'Generate .eml file to welcome countries to GIMS',
                    'options' => [
                        'route' => 'generate welcome',
                        'defaults' => [
                            'controller' => \Application\Controller\Email::class,
                            'action' => 'generateWelcome',
                        ],
                    ],
                ],
                'cache-clear' => [
                    'description' => 'Clear computing cache',
                    'options' => [
                        'route' => 'cache clear',
                        'defaults' => [
                            'controller' => \Application\Controller\Console::class,
                            'action' => 'cacheClear',
                        ],
                    ],
                ],
                'cache-warm-up' => [
                    'description' => 'Fill computing cache for all geonames for the given user (id or "anonymous")',
                    'options' => [
                        'route' => 'cache warm-up <userId>',
                        'defaults' => [
                            'controller' => \Application\Controller\Console::class,
                            'action' => 'cacheWarmUp',
                        ],
                    ],
                ],
                'cache-warm-up-one' => [
                    'description' => 'Fill computing cache only for the geoname specified by name for the given user (id or "anonymous")',
                    'options' => [
                        'route' => 'cache warm-up <userId> <geoname>',
                        'defaults' => [
                            'controller' => \Application\Controller\Console::class,
                            'action' => 'cacheWarmUpOne',
                        ],
                    ],
                ],
                'compute-population' => [
                    'description' => 'Compute population for geoname with children',
                    'options' => [
                        'route' => 'compute population',
                        'defaults' => [
                            'controller' => \Application\Controller\Console::class,
                            'action' => 'computePopulation',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
