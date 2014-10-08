<?php

namespace Application\Service\Calculator\Cache;

/**
 * Blackhole that does absolutely nothing at all (disable completely cache)
 */
class Blackhole extends \Zend\Cache\Storage\Adapter\BlackHole implements CacheInterface
{

    public function record($dependence)
    {
        // nothing to do
    }

    public function startComputing($dependent)
    {
        // nothing to do
    }

}
