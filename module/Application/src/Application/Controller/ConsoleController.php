<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ConsoleController extends AbstractActionController
{

    /**
     * Import data from file
     */
    public function importJmpAction()
    {
        $filename = $this->getRequest()->getParam('file');

        $importer = new \Application\Service\Importer\Jmp();
        $importer->setServiceLocator($this->getServiceLocator());

        return $importer->import($filename);
    }

}
