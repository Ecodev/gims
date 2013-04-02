<?php

namespace ApplicationTest\Repository;

class SettingRepositoryTest extends AbstractRepository
{

    public function testCanCRUDSetting()
    {
        $id = 'test id';
        $value = 'test value';
        $repository = $this->getEntityManager()->getRepository('Application\Model\Setting');

        // Get non-existing setting
        $newValue = $repository->get($id);
        $this->assertNull($newValue, 'value of non-existing setting should be null');

        // Set value
        $repository->set($id, $value);
        $reloadedSetting = $repository->get($id);
        $this->assertEquals($value, $reloadedSetting, 'value of fetched setting from DB should be correct');

        // Delete
        $deletedSetting = $repository->find($id);
        $this->getEntityManager()->remove($deletedSetting);
        $deletedValue = $repository->get($id);
        $this->assertNull($deletedValue, 'value of deleted setting should be null again');
        
        $this->getEntityManager()->flush();
    }

}
