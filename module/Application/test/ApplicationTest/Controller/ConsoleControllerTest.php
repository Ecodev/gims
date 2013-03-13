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
        // Everything is relative to the application root now.
        chdir(__DIR__ . '/../../../../../');
        $this->setApplicationConfig(
                include 'config/application.config.php'
        );

        parent::setUp();

        // Don't forget to call trait's method
        $this->setUpTransaction();
    }

    public function testNoArgumentDisplayUsage()
    {
        $this->dispatch('');
        $this->assertConsoleOutputContains('Usage:');
        $this->assertConsoleOutputContains('phpunit import jmp');
    }

    public function testJmpImport()
    {
        $this->dispatch('import jmp ' . __DIR__ . '/../../data/import_jmp.xlsx');
        $this->assertConsoleOutputContains('Total questionnaire: 3');
    }
    
    public function testPopulationImport()
    {
        $this->dispatch('import population ' . __DIR__ . '/../../data/population_urban.xlsx ' . __DIR__ . '/../../data/population_rural.xlsx ' . __DIR__ . '/../../data/population_total.xlsx');
        $this->assertConsoleOutputContains('18 population data imported');
    }

}
