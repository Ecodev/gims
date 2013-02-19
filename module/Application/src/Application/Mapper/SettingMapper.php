<?php

namespace Application\Mapper;

class SettingMapper extends AbstractMapper
{

    /**
     * Returns a setting, or create an empty, unsaved one, if not found
     * @param integer $id
     * @return \Application\Model\Setting
     */
    public function fetch($id)
    {
        // Check if table exists
        $sql = new \Zend\Db\Sql\Select('pg_tables');
        $sql->where(array('tablename' => $this->tableGateway->getTable()));
        $tables = $this->tableGateway->getAdapter()->query($sql->getSqlString($this->tableGateway->getAdapter()->getPlatform()), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

        $setting = null;
        if ($tables->count()) {
            $rowset = $this->tableGateway->select(array('id' => $id));
            $setting = $rowset->current();
        }

        if (!$setting) {
            $setting = $this->createRow();
            $setting->id = $id;
            $setting->value = null;
        }

        return $setting;
    }

}
