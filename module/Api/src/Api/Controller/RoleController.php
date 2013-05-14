<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class RoleController extends AbstractRestfulController
{

    /**
     * @return array
     */
    protected function getJsonConfig()
    {
        return array_merge(array(
            'name'
                ), parent::getJsonConfig()
        );
    }


    /**
     * @param array $data
     *
     * @return mixed|void|JsonModel
     * @throws \Exception
     */
    public function create($data)
    {
        throw new Exception('Not implemented');
    }

    /**
     * @param int   $id
     * @param array $data
     *
     * @return mixed|JsonModel
     */
    public function update($id, $data)
    {
        throw new Exception('Not implemented');
    }

    /**
     * @param int $id
     *
     * @return mixed|JsonModel
     */
    public function delete($id)
    {
        throw new Exception('Not implemented');
    }

}
