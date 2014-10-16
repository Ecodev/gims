<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ConsoleController extends AbstractActionController
{

    use \Application\Traits\EntityManagerAware;

    /**
     * Import data from JMP file
     */
    public function importJmpAction()
    {
        $filename = $this->getRequest()->getParam('file');

        $importer = new \Application\Service\Importer\Jmp();
        $importer->setServiceLocator($this->getServiceLocator());

        return $importer->import($filename);
    }

    /**
     * Import data from JMP file
     */
    public function importGlassAction()
    {
        $importer = new \Application\Service\Importer\Glass();
        $importer->setServiceLocator($this->getServiceLocator());

        return $importer->import();
    }

    /**
     * Import data from population file
     */
    public function importPopulationAction()
    {
        $filename = $this->getRequest()->getParam('file');

        $importer = new \Application\Service\Importer\Population();
        $importer->setServiceLocator($this->getServiceLocator());

        return $importer->import($filename);
    }

    public function cacheClearAction()
    {
        $cache = $this->getServiceLocator()->get('Cache\Computing');
        $cache->flush();

        return 'computing cache cleared' . PHP_EOL;
    }

    public function cacheWarmUpAction()
    {
        $progress = function($i, $total) {
            $digits = 4;

            return str_pad($i, $digits, ' ', STR_PAD_LEFT) . '/' . str_pad($total, $digits, ' ', STR_PAD_LEFT);
        };

        $calculator = new \Application\Service\Calculator\Calculator();
        $calculator->setServiceLocator($this->getServiceLocator());
        $aggregator = new \Application\Service\Calculator\Aggregator();
        $aggregator->setCalculator($calculator);

        $parts = $this->getEntityManager()->getRepository('Application\Model\Part')->findAll();
        $filterSets = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findAll();
        $geonames = $this->getEntityManager()->getRepository('Application\Model\Geoname')->findAll();

        $total = count($parts) * count($filterSets) * count($geonames);

        $i = 1;
        foreach ($geonames as $geoname) {
            echo '          ' . $geoname->getName() . PHP_EOL;
            foreach ($filterSets as $filterSet) {
                $filters = $filterSet->getFilters()->toArray();
                echo '            ' . $filterSet->getName() . PHP_EOL;
                foreach ($parts as $part) {
                    echo $progress($i++, $total) . '     ' . $part->getName() . PHP_EOL;
                    $aggregator->computeFlattenAllYears($filters, $geoname, $part);
                }
            }
        }

        return 'done' . PHP_EOL;
    }

}
