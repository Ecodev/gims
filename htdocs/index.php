<?php

// Fallback to default timezone if none specified. This is required for Travis CI
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

require_once(__DIR__ . '/../module/debug.php');

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

// Init the application
$application = Zend\Mvc\Application::init(require 'config/application.config.php');

// we only run the application if this file was NOT included (otherwise, the file was included to access misc functions)
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) {
    $application->run();
}
