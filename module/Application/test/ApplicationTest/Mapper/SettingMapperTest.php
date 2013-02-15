<?php

namespace ApplicationTest\Model;

use Application\Mapper\SettingMapper;
use Application\Model\Setting;
use Zend\Db\ResultSet\ResultSet;
use PHPUnit_Framework_TestCase;

class SettingMapperTest extends PHPUnit_Framework_TestCase
{

    /**
     * Returns a ResultSet configured with current Model
     * @return \Zend\Db\ResultSet\ResultSet
     */
    protected function getResultSet()
    {
        preg_match('/([^\\\\]*)MapperTest$/', get_called_class(), $matches);
        $modelName = $matches[1];
        $tableName = lcfirst($modelName);
        $fullModelName = 'Application\Model\\' . $modelName;

        $dbAdapter = \ApplicationTest\Bootstrap::getServiceManager()->get('Zend\Db\Adapter\Adapter');
        $objectPrototype = new $fullModelName(array('id'), $tableName, $dbAdapter);
        $resultSet = new ResultSet();
        $resultSet->setArrayObjectPrototype($objectPrototype);
        
        return $resultSet;
    }

    public function testFetchAllReturnsAllSettings()
    {
        $resultSet = new ResultSet();
        $mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway', array('select'), array(), '', false);
        $mockTableGateway->expects($this->once())
                ->method('select')
                ->with()
                ->will($this->returnValue($resultSet));

        $settingMapper = new SettingMapper($mockTableGateway);

        $this->assertSame($resultSet, $settingMapper->fetchAll());
    }

    public function testCanRetrieveAnSettingByItsId()
    {
        $resultSet = $this->getResultSet();
        
        $setting = clone $resultSet->getArrayObjectPrototype();
        $setting->exchangeArray(array(
            'id' => 123,
            'value' => 'Interesting value of setting',
        ));
        $resultSet->initialize(array($setting));
        

        $mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway', array('select'), array(), '', false);
        $mockTableGateway->expects($this->once())
                ->method('select')
                ->with(array('id' => 123))
                ->will($this->returnValue($resultSet));

        $settingMapper = new SettingMapper($mockTableGateway);

        $this->assertSame($setting, $settingMapper->fetch(123));
    }

    public function testCanDeleteAnSettingByItsId()
    {
//        $mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway', array('delete'), array(), '', false);
//        $mockTableGateway->expects($this->once())
//                ->method('delete')
//                ->with(array('id' => 123));
//
//        $resultSet = $this->getResultSet();
//        
//        $setting = clone $resultSet->getArrayObjectPrototype();
//        $setting->delete();
//        $settingMapper = new SettingMapper($mockTableGateway);
//        $settingMapper->deleteSetting(123);
    }
//
//    public function testSaveSettingWillInsertNewSettingsIfTheyDontAlreadyHaveAnId()
//    {
//        $settingData = array('artist' => 'The Military Wives', 'title' => 'In My Dreams');
//        $setting = new Setting();
//        $setting->exchangeArray($settingData);
//
//        $mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway', array('insert'), array(), '', false);
//        $mockTableGateway->expects($this->once())
//                ->method('insert')
//                ->with($settingData);
//
//        $settingMapper = new SettingMapper($mockTableGateway);
//        $settingMapper->saveSetting($setting);
//    }
//
//    public function testSaveSettingWillUpdateExistingSettingsIfTheyAlreadyHaveAnId()
//    {
//        $settingData = array('id' => 123, 'artist' => 'The Military Wives', 'title' => 'In My Dreams');
//        $setting = new Setting();
//        $setting->exchangeArray($settingData);
//
//        $resultSet = new ResultSet();
//        $resultSet->setArrayObjectPrototype(new Setting());
//        $resultSet->initialize(array($setting));
//
//        $mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway', array('select', 'update'), array(), '', false);
//        $mockTableGateway->expects($this->once())
//                ->method('select')
//                ->with(array('id' => 123))
//                ->will($this->returnValue($resultSet));
//        $mockTableGateway->expects($this->once())
//                ->method('update')
//                ->with(array('artist' => 'The Military Wives', 'title' => 'In My Dreams'), array('id' => 123));
//
//        $settingMapper = new SettingMapper($mockTableGateway);
//        $settingMapper->saveSetting($setting);
//    }
//
//    public function testExceptionIsThrownWhenGettingNonexistentSetting()
//    {
//        $resultSet = new ResultSet();
//        $resultSet->setArrayObjectPrototype(new Setting());
//        $resultSet->initialize(array());
//
//        $mockTableGateway = $this->getMock('Zend\Db\TableGateway\TableGateway', array('select'), array(), '', false);
//        $mockTableGateway->expects($this->once())
//                ->method('select')
//                ->with(array('id' => 123))
//                ->will($this->returnValue($resultSet));
//
//        $settingMapper = new SettingMapper($mockTableGateway);
//
//        try {
//            $settingMapper->getSetting(123);
//        } catch (\Exception $e) {
//            $this->assertSame('Could not find row 123', $e->getMessage());
//            return;
//        }
//
//        $this->fail('Expected exception was not thrown');
//    }
}
