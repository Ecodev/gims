<?php

namespace Application\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LogFactory implements FactoryInterface
{

    /**
     * @var \Zend\Log\Logger
     */
    private $logger = null;

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @return \Zend\Log\Logger
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        if (!$this->logger) {
            // Log to file
            $this->logger = new \Zend\Log\Logger();
            $writer = new \Zend\Log\Writer\Stream('data/logs/all.log');
            $this->logger->addWriter($writer);

            // Log to browser's console
            $firePhpWriter = new \Zend\Log\Writer\FirePhp();
            $firePhpWriter->getFirePhp()->getFirePhp()->setOption('includeLineNumbers', false);
            $firePhpWriter->addFilter(new \Application\Log\Filter\Headers());
            $this->logger->addWriter($firePhpWriter);

            \Zend\Log\Logger::registerErrorHandler($this->logger, true);
        }

        return $this->logger;
    }

}
