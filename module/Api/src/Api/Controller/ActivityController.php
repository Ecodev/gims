<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class ActivityController extends AbstractChildRestfulController
{

    public function getList()
    {
        $parent = $this->getParent();

        $objects = $this->getRepository()->getAllWithPermission($this->params()->fromQuery('permission', 'read'), $this->params()->fromQuery('q'), $this->params('parent'), $parent);
        $jsonData = $this->paginate($objects);

        return new JsonModel($jsonData);
    }

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
