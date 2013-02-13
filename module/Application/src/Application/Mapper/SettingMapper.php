<?php

namespace Application\Mapper;

class SettingMapper extends AbstractMapper
{

    public function fetch($id)
    {
        try {
            $setting = parent::fetch($id);
        } catch (\Exception $e) {
            
            $setting = $this->createRow();
            $setting->id = $id;
            $setting->value = null;
        }
        
        return $setting;
    }

}
