<?php

namespace ApplicationTest\Controller;

use \ApplicationTest\Traits\TestWithTransaction;

class ConsoleControllerTest extends \Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase
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

    public function testNoArgumentDisplayUsage()
    {
        $this->dispatch('');
        $this->assertConsoleOutputContains('Usage:');
        $this->assertConsoleOutputContains('Import individual country file in JMP format');
    }

    public function testJmpImport()
    {
        // @TODO find out how to test with database or mock it. 
        // For now it seems that the global configuration is not properly loaded for some reason
//        $this->dispatch('import jmp ' . realpath(__DIR__ . '/../../data/import_jmp.xlsx'));
//        $c = $this->getResponse()->getContent();
//        var_dump($c);
//        $this->assertConsoleOutputContains('Total questionnaire: 3');
    }

}
