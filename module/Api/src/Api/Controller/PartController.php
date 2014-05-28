<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class PartController extends AbstractRestfulController
{

    /**
     * @param int $id
     *
     * @return JsonModel
     */
    public function delete($id)
    {
        $this->getResponse()->setStatusCode(403);

        return new JsonModel(array('message' => 'Nobody can delete part'));
    }

}
