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
return array(
    'db' => array(
        'driver' => 'Pdo',
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
            'Zend\Log' => function ($sm) {

                // Log to file
                $logger = new Zend\Log\Logger();
                $writer = new Zend\Log\Writer\Stream('data/logs/all.log');
                $logger->addWriter($writer);

                // Log to browser's console
                $firePhpWriter = new Zend\Log\Writer\FirePhp();
                $logger->addWriter($firePhpWriter);
                $filter = new Application\Log\Filter\Headers();
                $firePhpWriter->addFilter($filter);

                Zend\Log\Logger::registerErrorHandler($logger, true);

                return $logger;
            },
        ),
    ),
);
