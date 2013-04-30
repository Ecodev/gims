<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class FilterController extends AbstractRestfulController
{

    protected function getJsonConfig()
    {
        return array(
            'name',
            'isOfficial',
            'children' => '__recursive',
            'summands' => array(
                'name',
            ),
        );
    }

    public function getList()
    {
        $filters = $this->getRepository()->getOfficialRoots();

        return new JsonModel($this->arrayOfObjectsToArray($filters, $this->getJsonConfig()));
    }

}
