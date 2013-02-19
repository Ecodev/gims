<?php

namespace ApplicationTest\Mapper;

use Application\Mapper\SettingMapper;
use Application\Model\Setting;

class SettingMapperTest extends AbstractMapper
{

    public function testFetchAllReturnsAllSettings()
    {
        $resultSet = $this->getResultSet();
        $mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway', array('select'), array(), '', false);
        $mockTableGateway->expects($this->once())
                ->method('select')
                ->with()
                ->will($this->returnValue($resultSet));

        $settingMapper = new SettingMapper($mockTableGateway);

        $this->assertSame($resultSet, $settingMapper->fetchAll());
    }

    public function testCanCRUDSetting()
    {
        $id = 'test id';
        $value = 'test value';
        $mapper = \ApplicationTest\Bootstrap::getServiceManager()->get('Application\Mapper\SettingMapper');

        // Create
        $setting = $mapper->fetch($id);
        $this->assertNull($setting->value, 'value of new setting should be null');
        $setting->value = $value;
        $setting->save();

        // Fetch
        $reloadedSetting = $mapper->fetch($id);
        $this->assertEquals($value, $reloadedSetting->value, 'value of fetched setting from DB should be correct');

        // Delete
        $reloadedSetting->delete();
        $deletedSetting = $mapper->fetch($id);
        $this->assertNull($deletedSetting->value, 'value of deleted setting should be null again');
    }

}
