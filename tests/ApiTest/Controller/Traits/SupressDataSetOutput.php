<?php

namespace ApiTest\Controller\Traits;

trait SupressDataSetOutput
{

    /**
     * Override parent to never output data set, because they are huge JSON file, which are not very interesting
     * @param boolean $includeData
     * @return string
     */
    public function getDataSetAsString($includeData = true)
    {
        return parent::getDataSetAsString(false);
    }

}
