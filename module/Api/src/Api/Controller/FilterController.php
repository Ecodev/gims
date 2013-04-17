<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class FilterController extends AbstractRestfulController
{

    protected function getJsonConfig()
    {
        return array(
            'name',
            'categoryFilterComponents' => array(
                'name',
                'categories' => array(
                    'name',
                ),
            ),
        );
    }

}
