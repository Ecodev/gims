<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class FilterSetController extends AbstractRestfulController
{

    protected function getJsonConfig()
    {
        return array(
            'name',
            'filters' => array(
                'name',
                'isOfficial',
                'children' => '__recursive',
            ),
        );
    }

}
