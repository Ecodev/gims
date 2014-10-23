<?php

return array(
    'Calculator\Cache' => [
        'enabled' => true,
        'namespace' => 'gims',
    ],
    'compressJavaScript' => true,
    'bodyCssClass' => null,
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOPgSql\Driver',
                'params' => array(
                    'host' => 'localhost',
                    'user' => 'postgres',
                    'dbname' => 'gims',
                    'port' => 5432,
                ),
                'doctrine_type_mappings' => array(
                    'geometry' => 'geometry',
                    'point' => 'point',
                    'polygon' => 'polygon',
                    'linestring' => 'linestring',
                    'questionnaire_status' => 'questionnaire_status',
                    'survey_type' => 'survey_type',
                ),
            ),
        ),
        'driver' => array(
            'application_entities' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/Application/Model')
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Application\Model' => 'application_entities'
                ),
            ),
        ),
        'configuration' => array(
            'orm_default' => array(
                'naming_strategy' => 'Application\Service\NamingStrategyFactory',
                'types' => array(
                    'geometry' => 'CrEOF\Spatial\DBAL\Types\GeometryType',
                    'point' => 'CrEOF\Spatial\DBAL\Types\Geometry\PointType',
                    'polygon' => 'CrEOF\Spatial\DBAL\Types\Geometry\PolygonType',
                    'linestring' => 'CrEOF\Spatial\DBAL\Types\Geometry\LineStringType',
                    'questionnaire_status' => 'Application\DBAL\Types\QuestionnaireStatusType',
                    'survey_type' => 'Application\DBAL\Types\SurveyTypeType',
                ),
                'proxy_dir' => 'data/cache/DoctrineORMModule/Proxy',
                'generate_proxies' => false,
                'datetime_functions' => array(
                    'CAST' => 'Application\ORM\AST\Functions\CastFunction',
                ),
                'string_functions' => array(
                    'CAST' => 'Application\ORM\AST\Functions\CastFunction',
                ),
                'numeric_functions' => array(
                    'CAST' => 'Application\ORM\AST\Functions\CastFunction',
                ),
            ),
        ),
        // migrations configuration
        'migrations_configuration' => array(
            'orm_default' => array(
                'directory' => 'data/migrations',
                'name' => 'GIMS Migrations',
                'namespace' => 'DoctrineMigrations',
                'table' => 'version',
            ),
        ),
    ),
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'index',
                    ),
                ),
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            //
            // It will never be matched for incoming URL, but it will be used to assemble URL
            'application' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/application',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
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
                            ),
                        ),
                    ),
                ),
            ),
            // Anything which is not API, export or template are redirected to Application\Controller\Index::indexAction()
            'angularjs_layout' => array(
                'type' => 'Regex',
                'options' => array(
                    'regex' => '^(?!(/api|/export|/template|/ocra_service_manager_yuml))(?<anything>.*)',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Index',
                        'action' => 'index',
                    ),
                    'spec' => '/%anything%',
                ),
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'template_application' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/template/application',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
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
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
            'ViewExcelRenderer' => 'Application\Service\ViewExcelRendererFactory',
            'ViewExcelStrategy' => 'Application\Service\ViewExcelStrategyFactory',
            'Application\Service\NamingStrategyFactory' => 'Application\Service\NamingStrategyFactory',
            'Calculator\Cache' => 'Application\Service\Calculator\Cache\Factory',
        ),
    ),
    'translator' => array(
        'locale' => 'en',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\Console' => 'Application\Controller\ConsoleController',
            'Application\Controller\Email' => 'Application\Controller\EmailController',
            'zfcuser' => 'Application\Controller\AuthController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'headLink' => 'Application\View\Helper\HeadLink',
            'headScript' => 'Application\View\Helper\HeadScript',
            'metadata' => 'Application\View\Helper\Metadata',
            'crudButtons' => 'Application\View\Helper\CrudButtons',
            'bodyCssClass' => 'Application\View\Helper\BodyCssClass',
            'version' => 'Application\View\Helper\Version',
            'googleAnalytics' => 'Application\View\Helper\GoogleAnalytics',
            'helpButton' => 'Application\View\Helper\HelpButton',
            'helpBox' => 'Application\View\Helper\HelpBox',
        ),
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'import-jmp' => array(
                    'description' => 'Import individual country file in JMP format',
                    'options' => array(
                        'route' => 'import jmp <file>',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Console',
                            'action' => 'importJmp'
                        ),
                    ),
                ),
                'import-glass' => array(
                    'description' => 'Import filters for GLASS and affect them to existing question',
                    'options' => array(
                        'route' => 'import glass',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Console',
                            'action' => 'importGlass'
                        ),
                    ),
                ),
                'import-population' => array(
                    'description' => 'Import population data',
                    'options' => array(
                        'route' => 'import population <file>',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Console',
                            'action' => 'importPopulation'
                        ),
                    ),
                ),
                'email' => array(
                    'description' => 'Send email',
                    'options' => array(
                        'route' => 'email <action> <id>',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Email',
                        ),
                    ),
                ),
                'cache-clear' => array(
                    'description' => 'Clear computing cache',
                    'options' => array(
                        'route' => 'cache clear',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Console',
                            'action' => 'cacheClear'
                        ),
                    ),
                ),
                'cache-warm-up' => array(
                    'description' => 'Fill computing cache for all geonames',
                    'options' => array(
                        'route' => 'cache warm-up',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Console',
                            'action' => 'cacheWarmUp'
                        ),
                    ),
                ),
                'cache-warm-up-one' => array(
                    'description' => 'Fill computing cache only for the geoname specified by name',
                    'options' => array(
                        'route' => 'cache warm-up <geoname>',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Console',
                            'action' => 'cacheWarmUpOne'
                        ),
                    ),
                ),
                'compute-population' => array(
                    'description' => 'Compute population for geoname with children',
                    'options' => array(
                        'route' => 'compute population',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Console',
                            'action' => 'computePopulation'
                        ),
                    ),
                ),
            ),
        ),
    ),
);
