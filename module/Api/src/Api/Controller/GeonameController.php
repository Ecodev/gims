<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class GeonameController extends AbstractRestfulController
{

    protected function getJsonConfig()
    {
        return array(
            'name',
        );
    }

}
