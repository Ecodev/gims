<?php

namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\View\Strategy\ExcelStrategy;

class ViewExcelStrategyFactory implements FactoryInterface
{

    /**
     * Create and return the EXCEL view strategy
     *
     * Retrieves the ViewExcelRenderer service from the service locator, and
     * injects it into the constructor for the EXCEL strategy.
     *
     * It then attaches the strategy to the View service, at a priority of 100.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ExcelStrategy
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $excelRenderer = $serviceLocator->get('ViewExcelRenderer');
        $excelStrategy = new ExcelStrategy($excelRenderer);
        return $excelStrategy;
    }

}
