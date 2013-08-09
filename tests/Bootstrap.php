<?php

// Setup autoloading
require __DIR__ . '/../init_autoloader.php';
require __DIR__ . '/../module/debug.php';

use Zend\Loader\StandardAutoloader;

$autoloader = new StandardAutoloader();
$autoloader->registerNamespace('ApplicationTest', __DIR__ . '/ApplicationTest/');
$autoloader->registerNamespace('ApiTest', __DIR__ . '/ApiTest/');
$autoloader->register();
