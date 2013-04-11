<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class SurveyController extends AbstractRestfulController
{

    /**
     * @return array
     */
    protected function getJsonConfig()
    {
        return array(
            'name',
            'code',
            'active',
            'year',
        );
    }

}
