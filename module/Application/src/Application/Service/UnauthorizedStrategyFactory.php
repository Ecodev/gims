<?php

namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\View\UnauthorizedStrategy;

class UnauthorizedStrategyFactory implements FactoryInterface
{
    /**
     * @param  ServiceLocatorInterface $sl
     * @return UnauthorizedStrategy
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $rbacService = $sl->get('ZfcRbac\Service\Rbac');

        $strategy = new UnauthorizedStrategy();
        $strategy->setUnauthorizedTemplate($rbacService->getOptions()->getTemplate());

        return $strategy;
    }
}
