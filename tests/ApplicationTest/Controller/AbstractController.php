<?php

namespace ApplicationTest\Controller;
use \ApplicationTest\Traits\TestWithTransaction;

class AbstractController extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{
    use TestWithTransaction {
        TestWithTransaction::setUp as setUpTransaction;
    }

    public function setUp()
    {
        // Everything is relative to the application root now.
        chdir(__DIR__ . '/../../../');


        // Config is the normal configuration, overridden by test configuration
        $config = include 'config/application.config.php';
        $config['module_listener_options']['config_glob_paths'][] = 'config/autoload/{,*.}{phpunit}.php';

        $this->setApplicationConfig($config);

        parent::setUp();

        // Don't forget to call trait's method
        $this->setUpTransaction();
    }

}
