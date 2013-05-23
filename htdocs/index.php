<?php

// Fallback to default timezone if none specified. This is required for Travis CI
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
