<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class CategoryController extends AbstractRestfulController
{

    protected function getJsonConfig()
    {
        return array(
            'name',
            'official',
            'children' => '__recursive',
            'summands' => array('name'),
        );
    }

    public function getList()
    {
        $categorys = $this->getRepository()->findBy(array(
            'parent' => null,
            'official' => true,
        ));

        return new JsonModel($this->arrayOfObjectsToArray($categorys, $this->getJsonConfig()));
    }

}
