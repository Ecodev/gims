<?php

namespace Application\Service\Calculator\Cache;

/**
 * Blackhole that does absolutely nothing at all (disable completely cache)
 */
class BlackHole implements CacheInterface
{

    public function getItem($key)
    {
        // nothing to do
    }

    public function hasItem($key)
    {
        return false;
    }

    public function record($dependence)
    {
        // nothing to do
    }

    public function removeItem($key)
    {
        // nothing to do
    }

    public function removeItems(array $keys)
    {
        // nothing to do
    }

    public function setItem($key, $value)
    {
        // nothing to do
    }

    public function startComputing($dependent)
    {
        // nothing to do
    }

}
