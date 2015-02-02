<?php

namespace ApplicationTest\Traits;

/**
 * Allow to run test within a database transaction, so database will be unchanged after test
 */
trait TestWithTransaction
{

    /**
     * Get EntityManager
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->getApplicationServiceLocator()->get('Doctrine\ORM\EntityManager');
    }

    /**
     * Start transaction
     */
    public function setUp()
    {
        $this->getEntityManager()->beginTransaction();
    }

    /**
     * Cancel transaction, to undo all changes made
     */
    public function tearDown()
    {
        $this->getEntityManager()->rollback();
        $this->getEntityManager()->clear();
        $this->getEntityManager()->getConnection()->close();
    }
}
