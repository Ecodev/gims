<?php

namespace Application\Service\Calculator\Cache;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for cache service specialized for computing
 */
class Factory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config')['Calculator\Cache'];
        if ($config['enabled']) {
            $cache = new Redis($config['namespace']);
        } else {
            $cache = new BlackHole();
        }

        return $cache;
    }

}
