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
     * Preload database with minimum population if not exists yet
     */
    protected function preloadPopulation()
    {
        $country = $this->getEntityManager()->getRepository('Application\Model\Country')->findOneByName('Switzerland');
        $parts = array(
            $this->partUrban = $this->getEntityManager()->getRepository('Application\Model\Part')->getOrCreate('Urban'),
            $this->partRural = $this->getEntityManager()->getRepository('Application\Model\Part')->getOrCreate('Rural'),
            null,
        );
        $this->getEntityManager()->flush();
        $populationRepository = $this->getEntityManager()->getRepository('Application\Model\Population');

        foreach ($parts as $part) {
            foreach (array(1997, 2003) as $year) {
                $population = $populationRepository->findOneBy(array(
                    'year' => $year,
                    'country' => $country,
                    'part' => $part,
                ));

                if (!$population) {
                    $population = new \Application\Model\Population();
                    $population->setPopulation(12345)
                            ->setYear($year)
                            ->setCountry($country)
                            ->setPart($part);

                    $this->getEntityManager()->persist($population);
                }
            }
        }

        $this->getEntityManager()->flush();
    }

    public function testJmpImport()
    {
        $this->preloadPopulation();
        $this->dispatch('import jmp ' . __DIR__ . '/../../data/import_jmp.xlsx');
        $this->assertConsoleOutputContains('Total imported: 2 questionnaires, 66 answers, 10 exclude rules, 2 ratio rules, 2 estimate rules');
    }

    public function testPopulationImport()
    {
        $this->dispatch('import population ' . __DIR__ . '/../../data/population_urban.xlsx ' . __DIR__ . '/../../data/population_rural.xlsx ' . __DIR__ . '/../../data/population_total.xlsx');
        $this->assertConsoleOutputContains('54 population data imported');
    }

}
