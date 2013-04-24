<?php

namespace Application\DBAL\Logging;

/**
 * A SQL logger that forward logs to Zend Log
 */
class ForwardSQLLogger implements \Doctrine\DBAL\Logging\SQLLogger
{

    /**
     * @var \Zend\Log\LoggerInterface
     */
    private $logger;

    /**
     * Set the logger
     * @param \Zend\Log\LoggerInterface $logger
     */
    public function setLogger(\Zend\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Returns the actual logger
     * @return \Zend\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        // Here we cannot use debug() level, because it would produce a stacktrace for each call via FirePHP, thus producing gigantic HTTP headers
        $this->getLogger()->info($sql);
        $this->getLogger()->info($params);
        $this->getLogger()->info($types);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {

    }

}
