<?php

// Setup autoloading
require __DIR__ . '/../../../init_autoloader.php';

use Zend\Loader\StandardAutoloader;

$autoloader = new StandardAutoloader();
$autoloader->registerNamespace('ApplicationTest', __DIR__ . '/ApplicationTest/');
$autoloader->register();
