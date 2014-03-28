<?php

namespace Api\Controller\Rule;

use Zend\View\Model\JsonModel;
use Api\Controller\AbstractChildRestfulController;

class FilterQuestionnaireUsageController extends AbstractChildRestfulController
{

    /**
     * @param int   $id
     * @param array $data
     *
     * @return mixed|JsonModel
     */
    public function update($id, $data)
    {
        throw new \Exception('Not implemented');
    }

}
