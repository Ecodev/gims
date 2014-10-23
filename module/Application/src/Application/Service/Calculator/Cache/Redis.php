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
class Redis implements CacheInterface
{

    private $redis;

    /**
     * @var array
     */
    private $volatile = [];

    /**
     * @var array [dependent => [dependencies]]
     */
    private $dependencies = [];

    public function __construct($namespace)
    {
        $this->redis = new \Redis();
        $success = $this->redis->connect('localhost');
        $this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
        $this->redis->setOption(\Redis::OPT_PREFIX, $namespace . ':');
        $this->redis->setOption(\Redis::OPT_SCAN, \Redis::SCAN_RETRY);
        if (!$success) {
            throw new Exception\RuntimeException('Could not estabilish connection with Redis instance');
        }
    }

    /**
     * Flush currently set namespace
     *
     * @return integer delete keys count
     */
    public function flush()
    {
        $this->volatile = [];

        $it = null; // Initialize our iterator to NULL
        $count = 0;

        // Temporarly disable prefix
        $pattern = $this->redis->_prefix('*');
        $previousPrefix = $this->redis->getOption(\Redis::OPT_PREFIX);
        $this->redis->setOption(\Redis::OPT_PREFIX, '');

        while ($keys = $this->redis->scan($it, $pattern)) {
            foreach ($keys as $key) {
                $this->redis->del($key);
                $count++;
            }
        }

        // Restore prefix
        $this->redis->setOption(\Redis::OPT_PREFIX, $previousPrefix);

        return $count;
    }

    /**
     * Get an item.
     * @param string $key
     * @return mixed
     */
    public function getItem($key)
    {
        $this->record($key);
        if (array_key_exists($key, $this->volatile)) {
            return $this->volatile[$key];
        }

        $value = $this->redis->get($key);
        $this->volatile[$key] = $value;

        return $value;
    }

    /**
     * Test if an item exists.
     * @param string $key
     * @return boolean
     */
    public function hasItem($key)
    {
        return array_key_exists($key, $this->volatile) || $this->redis->exists($key);
    }

    /**
     * Store an item and create its list of  dependencies
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public function setItem($key, $value)
    {
        $this->volatile[$key] = $value;
        $success = $this->redis->set($key, $value);

        // If what we store gathered dependencies, then store those in cache
        // and stop recording them
        if (array_key_exists($key, $this->dependencies)) {
            foreach ($this->dependencies[$key] as $dependency) {
                $depKey = $this->getDependencyKey($dependency);
                $this->redis->sAdd($depKey, $key);
            }

            unset($this->dependencies[$key]);
        }
        $this->record($key);

        return $success;
    }

    /**
     * Remove an item and things depending on it
     * @param string $key
     * @return string
     */
    public function removeItem($key)
    {
        unset($this->volatile[$key]);
        $result = $this->redis->del($key);
        $depKey = $this->getDependencyKey($key);

        // If things depends on that key, then also remove depending things
        if ($this->hasItem($depKey)) {
            $deps = $this->redis->sMembers($depKey);
            $this->redis->del($depKey);

            $this->removeItems($deps);
        }

        return $result;
    }

    public function removeItems(array $keys)
    {
        foreach ($keys as $key) {
            $this->removeItem($key);
        }
    }

    /**
     * Add the $dependent to the list of things being currently computed.
     *
     * It will be automatically removed from the list when setItem()
     * is called with the same key
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

}
