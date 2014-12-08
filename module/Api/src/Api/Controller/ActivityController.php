<?php

namespace Api\Controller;

class ActivityController extends AbstractRestfulController
{

    public function create($data)
    {
        throw new \Application\Service\PermissionDeniedException('activity cannot be created');
    }

    public function update($id, $data)
    {
        throw new \Application\Service\PermissionDeniedException('activity cannot be updated');
    }

    public function delete($id)
    {
        throw new \Application\Service\PermissionDeniedException('activity cannot be deleted');
    }

}
