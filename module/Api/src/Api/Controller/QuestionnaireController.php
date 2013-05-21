<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class QuestionnaireController extends AbstractRestfulController
{

    protected function getJsonConfig()
    {
        return array(
            'name',
            'dateObservationStart',
            'dateObservationEnd',
            'survey' => array(
                'code',
                'name'
            ),
            'geoname' => array('name'),
        );
    }
}
