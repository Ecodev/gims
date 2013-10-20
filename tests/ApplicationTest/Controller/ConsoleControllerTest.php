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
        chdir(__DIR__ . '/../../../');
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

    /**
     * Truncate database to be sure that we create all objects from scratch
     */
    protected function truncateDatabase()
    {
        $this->getEntityManager()->getConnection()->executeQuery('TRUNCATE survey CASCADE;');
        $this->getEntityManager()->getConnection()->executeQuery('TRUNCATE rule CASCADE;');
        $this->getEntityManager()->getConnection()->executeQuery('TRUNCATE filter CASCADE;');
    }

    public function testJmpImport()
    {
        $this->truncateDatabase();
        $this->dispatch('import jmp ' . __DIR__ . '/../../data/import_jmp.xlsx');

        $expected = <<<STRING
Surveys          : 1
Questionnaires   : 2
Alternate Filters: 18
Answers          : 70
Formulas         : 35
Uses of Exclude  : 11
Uses of Rule for Filter          : 33
Uses of Formula for Filter       : 21
Uses of Formula for Questionnaire: 20
STRING;
        $this->assertConsoleOutputContains($expected);
    }

    public function testPopulationImport()
    {
        $this->dispatch('import population ' . __DIR__ . '/../../data/population_urban.xlsx ' . __DIR__ . '/../../data/population_rural.xlsx ' . __DIR__ . '/../../data/population_total.xlsx');
        $this->assertConsoleOutputContains('54 population data imported');
    }

}
