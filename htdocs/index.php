<?php
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));
define('DOCUMENT_ROOT', __DIR__);

if (!defined('APPLICATION_ENV'))
{
	if (isset($_SERVER['SERVER_NAME']))
		if (preg_match("/^(www\.)?gims\.pro$/", $_SERVER['SERVER_NAME']))
			define('APPLICATION_ENV', 'production');
		elseif (strpos( $_SERVER['SERVER_NAME'], 'dev.') === 0)
			define('APPLICATION_ENV', 'testing');
		else
			define('APPLICATION_ENV', 'development');
	else
		define('APPLICATION_ENV', 'production'); // default
}

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
