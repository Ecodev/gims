<?php

namespace ApplicationTest\Mapper;

use Zend\Db\ResultSet\ResultSet;
use PHPUnit_Framework_TestCase;

abstract class AbstractMapper extends PHPUnit_Framework_TestCase
{
    use \ApplicationTest\Traits\TestWithTransaction;

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
}
