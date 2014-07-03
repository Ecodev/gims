<?php

namespace ApplicationTest\Controller;

use \ApplicationTest\Traits\TestWithTransaction;

/**
 * @group Console
 */
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
Surveys          : 2
Questionnaires   : 3
Answers          : 70
Rules            : 44
Uses of Exclude  : 41
Uses of Rule for Questionnaire       : 20
Uses of Rule for Filter-Questionnaire: 45
Uses of Rule for Filter-Geoname      : 26
STRING;
        $this->assertConsoleOutputContains($expected);
    }

    public function testPopulationImport()
    {
        $this->dispatch('import population ' . __DIR__ . '/../../data/import_population.xlsx');
        $this->assertConsoleOutputContains('54 population data imported');
    }

}
