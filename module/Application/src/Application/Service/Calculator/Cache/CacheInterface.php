<?php

namespace Application\Service\Calculator\Cache;

use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\Storage\ClearByNamespaceInterface;

interface CacheInterface extends StorageInterface, ClearByNamespaceInterface
{

    /**
     * Add the $dependent to the list of things being currently computed.
     *
     * It will be automatically removed from the list when setItem() is called
     * with the same key
     * @param string $dependent
     */
    public function startComputing($dependent);

    /**
     * Record the dependence as used during computing.
     *
     * It will be associated with every single thing that
     *  is currently being computed.
     * @param string $dependence
     */
    public function record($dependence);
}
