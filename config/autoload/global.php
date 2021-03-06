<?php

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */
return [
    // Because Google Analytics is set up to filter by hostname,
    // we can share the same tracking code for all version of the site.
    // However it can still be overriden locally if needed (to disable it)
    'googleAnalyticsTrackingCode' => 'UA-52338137-1',
    'db' => [
        'driver' => 'Pdo',
    ],
    'service_manager' => [
        'factories' => [
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
            'Zend\Log' => \Application\Service\LogFactory::class,
            \Application\DBAL\Logging\ForwardSQLLogger::class => \Application\Service\ForwardSQLLoggerFactory::class,
        ],
    ],
    'session' => [
        'name' => 'gimscookie',
        'save_path' => __DIR__ . '/../../data/session',
        'cookie_httponly' => true,
    ],
];
