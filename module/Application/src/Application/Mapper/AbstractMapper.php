<?php

namespace Application\Mapper;

use Zend\Db\TableGateway\TableGateway;

class AbstractMapper
{
    /**
     * @var \Zend\Db\TableGateway\TableGateway 
     */
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * Returns a single Model from database
     * @param mixed $id
     * @return AbstractModel
     * @throws \Exception
     */
    public function fetch($id)
    {
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();

        if (!$row) {
            throw new \Exception("Could not find row $id");
        }

        return $row;
    }

    /**
     * Returns a ResultSet of all Model from database
     * @return ResultSet
     */
    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();

        return $resultSet;
    }

    /**
     * Create and returns a new row which not yet saved in database
     * @return AbstractModel
     */
    public function createRow()
    {
        
        $resultSet = $this->tableGateway->getResultSetPrototype();
        $newRow = clone $resultSet->getArrayObjectPrototype();
        
        // Populate row with all null values so they are get()table
//        $meta = new \Zend\Db\Metadata\Metadata($this->tableGateway->getAdapter());
//        $columns = $meta->getColumns($this->tableGateway->getTable());
//        foreach ($columns as $column)
//        {
//            $name = $column->getName();
//            $newRow->$name = null;
//        }
                
        return $newRow;
    }
}
