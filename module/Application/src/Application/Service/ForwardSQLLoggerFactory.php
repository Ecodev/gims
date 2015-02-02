<?php

namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ForwardSQLLoggerFactory implements FactoryInterface
{

    /**
     * @var \Application\DBAL\Logging\ForwardSQLLogger
     */
    private $logger = null;

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @return \Application\DBAL\Logging\ForwardSQLLogger
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        if (!$this->logger) {
            $this->logger = new \Application\DBAL\Logging\ForwardSQLLogger();
            $this->logger->setLogger($sl->get('Zend\Log'));
        }

        return $this->logger;
    }
}
