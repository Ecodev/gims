<?php

namespace Application\Service\Calculator\Cache;

interface CacheInterface
{

    /**
     * Get an item.
     *
     * @param  string  $key
     * @return mixed Data on success, null on failure
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function getItem($key);

    /**
     * Test if an item exists.
     *
     * @param  string $key
     * @return bool
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function hasItem($key);

    /**
     * Store an item.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return bool
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function setItem($key, $value);

    /**
     * Remove an item.
     *
     * @param  string $key
     * @return bool
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function removeItem($key);

    /**
     * Remove multiple items.
     *
     * @param  array $keys
     * @return array Array of not removed keys
     * @throws \Zend\Cache\Exception\ExceptionInterface
     */
    public function removeItems(array $keys);

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
