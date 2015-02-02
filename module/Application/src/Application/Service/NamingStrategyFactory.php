<?php

namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class NamingStrategyFactory implements FactoryInterface
{

    /**
     * @var \Doctrine\ORM\Mapping\UnderscoreNamingStrategy
     */
    private $strategy = null;

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @return \Doctrine\ORM\Mapping\UnderscoreNamingStrategy
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        if (!$this->strategy) {
            $this->strategy = new \Doctrine\ORM\Mapping\UnderscoreNamingStrategy();
        }

        return $this->strategy;
    }
}
