<?php

namespace ApplicationTest\Controller;

class AbstractController extends \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(
                include __DIR__ . '/../../../../../config/application.config.php'
        );
        parent::setUp();
    }
}
