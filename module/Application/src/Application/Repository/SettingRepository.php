<?php

namespace Application\Repository;

class SettingRepository extends AbstractRepository
{

    /**
     * Returns a setting, or create an empty, unsaved one, if not found
     * @param integer $id
     * @return \Application\Model\Setting
     */
    public function get($id)
    {
        $setting = $this->find($id);

        if ($setting)
            return $setting->getValue();
        else
            return null;
    }

    /**
     * Returns a setting, or create an empty, unsaved one, if not found
     * @param integer $id
     * @return \Application\Model\Setting
     */
    public function set($id, $value)
    {
        $setting = $this->find($id);

        if (!$setting) {
            $setting = new \Application\Model\Setting();
            $setting->setId($id);
            $this->getEntityManager()->persist($setting);
        }

        $setting->setValue($value);

        return $this;
    }

}
