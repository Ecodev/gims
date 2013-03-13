<?php

// Use Composer autoloading
require __DIR__ . '/vendor/autoload.php';

if (!class_exists('Zend\Loader\AutoloaderFactory')) {
    throw new RuntimeException('Unable to load ZF2. Run `php composer.phar install`.');
}
