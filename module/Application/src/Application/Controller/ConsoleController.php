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
        $filename = $this->getRequest()->getParam('file');

        $importer = new \Application\Service\Importer\Population();
        $importer->setServiceLocator($this->getServiceLocator());

        return $importer->import($filename);
    }

}
