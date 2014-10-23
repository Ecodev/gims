<?php

namespace ApplicationTest\Service\Calculator;

use Application\Service\Calculator\Cache\Redis;

/**
 * @group Calculator
 */
class RedisTest extends \ApplicationTest\Controller\AbstractController
{

    /**
     * @var \Application\Service\Calculator\Cache
     */
    private $cache;

    public function setUp()
    {
        parent::setUp();
        $this->cache = new Redis('phpunit');
        $this->cache->flush();
    }

    public function testCache()
    {
        // Simulate some computing process
        // We start to compute chart
        $this->cache->startComputing('chart');

        // but we need questionnaire:1, so we start computing it
        // and record everything used during computing
        $this->cache->startComputing('questionnaire:1');
        $this->cache->record('answer:1');
        $this->cache->record('answer:2');
        $this->cache->record('filter:1');
        $this->cache->setItem('questionnaire:1', 123);

        // same for questionnaire:2
        $this->cache->startComputing('questionnaire:2');
        $this->cache->record('answer:3');
        $this->cache->record('answer:4');
        $this->cache->record('filter:2');
        $this->cache->setItem('questionnaire:2', 456);

        // We finally get the result for chart, and store in cache for later.
        // At the same time everything used to compute chart will also be stored.
        $this->cache->setItem('chart', 789);

        $this->assertCache();
    }

    public function testIndirectCache()
    {
        // Simulate some computing process for two different users
        // First user want only questionnaire:1, so we compute and cache it
        $this->cache->startComputing('questionnaire:1');
        $this->cache->record('answer:1');
        $this->cache->record('answer:2');
        $this->cache->record('filter:1');
        $this->cache->setItem('questionnaire:1', 123);

        // Then later a second user wants chart, so we start computing it
        $this->cache->startComputing('chart');

        // We find that questionnaire:1 is already in cache, so re-used it
        // and mark it as used
        $this->cache->record('questionnaire:1');

        // Then keep going as usual for questionnaire:2
        $this->cache->startComputing('questionnaire:2');
        $this->cache->record('answer:3');
        $this->cache->record('answer:4');
        $this->cache->record('filter:2');
        $this->cache->setItem('questionnaire:2', 456);

        // Store the final result in cache
        $this->cache->setItem('chart', 789);

        // At this point the cache does not know directly that chart depends
        // on answer:1, but he still knows that chart depends on questionnaire:1
        // and that questionnaire:1 depends on answer:1, so the cache should be
        // able to properly destroy the cached chart if answer:1 is removed.
        $this->assertCache();
    }

    private function assertCache()
    {
        // We should get exactly the same things back from cache
        $this->assertSame(123, $this->cache->getItem('questionnaire:1'));
        $this->assertSame(456, $this->cache->getItem('questionnaire:2'));
        $this->assertSame(789, $this->cache->getItem('chart'));

        // After removing answer1, we should not have anything depending on it anymore
        $this->cache->removeItem('answer:1');
        $this->assertFalse($this->cache->hasItem('questionnaire:1'));
        $this->assertSame(456, $this->cache->getItem('questionnaire:2'));
        $this->assertFalse($this->cache->hasItem('chart'));
    }

}
