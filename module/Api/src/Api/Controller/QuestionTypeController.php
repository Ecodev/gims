<?php

namespace Api\Controller;

use Application\Model\QuestionType;
use Zend\View\Model\JsonModel;

class QuestionTypeController extends AbstractRestfulController
{

    public function getList()
    {
        $properties = QuestionType::getValues();
        $arrayValues = array();
        foreach($properties as $property)
            array_push($arrayValues, array(
                                            'text' => $property->__toString(),
                                            'value' => $property->__toString()
                                    ));

        return new JsonModel( $arrayValues );
    }

    /**
     * @param array $data
     *
     * @return mixed|void|JsonModel
     * @throws \Exception
     */
    public function create($data)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @param int $id
     * @param array $data
     *
     * @throws \Exception
     * @return mixed|JsonModel
     */
    public function update($id, $data)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @param int $id
     *
     * @throws \Exception
     * @return mixed|JsonModel
     */
    public function delete($id)
    {
        throw new \Exception('Not implemented');
    }

}
