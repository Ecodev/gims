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
        $rowset = $this->tableGateway->select(array('id' => $id));
        $setting = $rowset->current();

        if (!$setting) {
            $setting = $this->createRow();
            $setting->id = $id;
            $setting->value = null;
        }

        return $setting;
    }

}
