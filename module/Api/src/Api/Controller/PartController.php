<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class PartController extends AbstractRestfulController
{

    protected function getJsonConfig()
    {
        return array(
            'name',
        );
    }

}
