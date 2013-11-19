<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ConsoleController extends AbstractActionController
{

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
        $sources = array(
            'urban' => array(
                'file' => 'data/cache/population_urban.xls',
                'url' => 'http://esa.un.org/unpd/wup/cD-rom/WUP2011-F19-Urban_Population_Annual.xls',
            ),
            'rural' => array(
                'file' => 'data/cache/population_rural.xls',
                'url' => 'http://esa.un.org/unpd/wup/cD-rom/WUP2011-F20-Rural_Population_Annual.xls',
            ),
            'total' => array(
                'file' => 'data/cache/population_total.xls',
                'url' => 'http://esa.un.org/unpd/wup/cD-rom/WUP2011-F18-Total_Population_Annual.xls',
            ),
        );

        $urban = $this->getRequest()->getParam('urbanfile');
        $rural = $this->getRequest()->getParam('ruralfile');
        $total = $this->getRequest()->getParam('totalfile');

        if (!$urban || !$rural || !$total) {

            $allFilesFound = true;
            foreach ($sources as $source) {
                $allFilesFound &= is_readable($source['file']);
            }

            if ($allFilesFound) {
                echo "Using files found in data/cache/population_*" . PHP_EOL;
            } else {
                echo "Files not specifed. Downloading files from esa.un.org ..." . PHP_EOL;

                foreach ($sources as $source) {
                    echo $source['file'] . PHP_EOL;
                    $content = file_get_contents($source['url']);
                    file_put_contents($source['file'], $content);
                }
            }

        } else {
            $sources['urban']['file'] = $urban;
            $sources['rural']['file'] = $rural;
            $sources['total']['file'] = $total;
        }

        $importer = new \Application\Service\Importer\Population();
        $importer->setServiceLocator($this->getServiceLocator());

        return $importer->import($sources['urban']['file'], $sources['rural']['file'], $sources['total']['file']);
    }

}
