<?php

namespace ApplicationTest\Traits;

/**
 * Allow to run test within a database transaction, so database will be unchanged after test
 */
trait TestWithTransaction
{

    public function setUp()
    {
        $dbAdapter = \ApplicationTest\Bootstrap::getServiceManager()->get('Zend\Db\Adapter\Adapter');
        $dbAdapter->driver->getConnection()->beginTransaction();
    }

    public function tearDown()
    {
        $dbAdapter = \ApplicationTest\Bootstrap::getServiceManager()->get('Zend\Db\Adapter\Adapter');
        $dbAdapter->driver->getConnection()->rollback();
    }

}
