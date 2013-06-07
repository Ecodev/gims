<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class PartController extends AbstractRestfulController
{

    /**
     * @return JsonModel
     */
    public function getList()
    {
        $parts = $this->getRepository()->findAll();

        $array = $this->hydrator->extractArray($parts, $this->getJsonConfig());
        array_unshift($array, array('id' => -1, 'name' => 'Total'));

        return new JsonModel($array);
    }

}
