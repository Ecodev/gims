<?php

namespace Application\Service\Calculator\Cache;

/**
 * Cache service specialized for computing.
 *
 * The typical usagee is as follow:
 *
 *   $cache->startComputing()
 *   $cache->record()        // as many times as needed (usually done by Calculator)
 *   $cache->setItem()
 */
class Redis extends \Zend\Cache\Storage\Adapter\Redis implements CacheInterface
{

    /**
     * @var array
     */
    private $volatile = [];

    /**
     * @var array [dependent => [dependencies]]
     */
    private $dependencies = [];

    /**
     * Override parent to provide a first level cache in memory
     * @param string $normalizedKey
     * @param boolean $success
     * @param string $casToken
     * @return mixed
     */
    protected function internalGetItem(&$normalizedKey, &$success = null, &$casToken = null)
    {
        $this->record($normalizedKey);
        if (array_key_exists($normalizedKey, $this->volatile)) {
            $success = true;
            $value = $this->volatile[$normalizedKey];
            $casToken = $value;

            return $value;
        }

        $value = parent::internalGetItem($normalizedKey, $success, $casToken);
        if ($success) {
            $this->volatile[$normalizedKey] = $value;
        }

        return $value;
    }

    /**
     * Override parent to provide a first level cache in memory
     * @param string $normalizedKey
     * @return boolean
     */
    protected function internalHasItem(&$normalizedKey)
    {
        return array_key_exists($normalizedKey, $this->volatile) || parent::internalHasItem($normalizedKey);
    }

    /**
     * Override parent to provide creation of dependencies list
     * @param type $normalizedKey
     * @param type $value
     * @return type
     */
    protected function internalSetItem(&$normalizedKey, &$value)
    {
        $this->volatile[$normalizedKey] = $value;
        $success = parent::internalSetItem($normalizedKey, $value);

        // If what we store gathered dependencies, then store those in cache
        // and stop recording them
        if (array_key_exists($normalizedKey, $this->dependencies)) {
            foreach ($this->dependencies[$normalizedKey] as $dependency) {
                $setKey = $this->getDependencyKey($dependency);
                $this->getRedisResource()->sAdd($this->getPrefixedKey($setKey), $normalizedKey);
            }

            unset($this->dependencies[$normalizedKey]);
        }
        $this->record($normalizedKey);

        return $success;
    }

    /**
     * Remove an item and things depending on it
     * @param string $normalizedKey
     * @return string
     */
    public function internalRemoveItem(&$normalizedKey)
    {
        unset($this->volatile[$normalizedKey]);
        $result = parent::internalRemoveItem($normalizedKey);
        $depKey = $this->getDependencyKey($normalizedKey);

        // If things depends on that key, then also remove depending things
        if ($this->internalHasItem($depKey)) {
            $deps = $this->sMembers($depKey);
            parent::internalRemoveItem($depKey);

            $this->internalRemoveItems($deps);
        }

        return $result;
    }

    /**
     * Add the $dependent to the list of things being currently computed.
     *
     * It will be automatically removed from the list when setItem() is called
     * with the same key
     * @param string $dependent
     */
    public function startComputing($dependent)
    {
        $this->dependencies[$dependent] = [];
    }

    /**
     * Record the dependence as used during computing.
     *
     * It will be associated with every single thing that
     *  is currently being computed.
     * @param string $dependence
     */
    public function record($dependence)
    {
        foreach ($this->dependencies as &$d) {
            $d[] = $dependence;
        }
    }

    /**
     * From the standard key, return the key for dependencies
     * @param string $key
     * @return string key for dependencies
     */
    private function getDependencyKey($key)
    {
        return $key . ':dep';
    }

    /**
     * Return an array of values contained in the set
     * @param string $key
     * @return array
     */
    private function sMembers($key)
    {
        $normalizedKey = $this->getPrefixedKey($key);

        return $this->getRedisResource()->sMembers($normalizedKey);
    }

    /**
     * Returned the key prefixed with namespace
     * @param string $key
     * @return string $key prefixed with namespace
     */
    private function getPrefixedKey($key)
    {
        $this->normalizeKey($key);

        return $this->namespacePrefix . $key;
    }

}
