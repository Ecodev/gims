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
        throw new \Application\Service\PermissionDeniedException('Nobody can delete part');
    }

}
