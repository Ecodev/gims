<?php

namespace ApplicationTest\Controller;

class ConsoleControllerTest extends \Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(
                include __DIR__ . '/../../../../../config/application.config.php'
        );
        
        parent::setUp();
    }

    public function testNoArgumentDisplayUsage()
    {
        $this->dispatch('');
        $this->assertConsoleOutputContains('Usage:');
        $this->assertConsoleOutputContains('Update database schema');
    }

    public function testDatabaseUpdate()
    {
        // @TODO find out how to test with database or mock it. 
        // For now it seems that the global configuration is not properly loaded for some reason
//        $this->getApplicationServiceLocator()->setFactory( 'Zend\Db\Adapter\Adapter', 'Zend\Db\Adapter\AdapterServiceFactory');
//        $this->dispatch('database update');
//        $this->assertConsoleOutputContains('current version is:');
    }

}
