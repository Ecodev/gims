<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class CountryController extends AbstractRestfulController
{

    protected function getJsonConfig()
    {
        return array(
            'code',
            'name',
        );
    }

}
