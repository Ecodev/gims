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
        $this->setApplicationConfig(
                include __DIR__ . '/../../../../../config/application.config.php'
        );
        parent::setUp();
      
        // Don't forget to call trait's method
        $this->setUpTransaction();
    }

}
