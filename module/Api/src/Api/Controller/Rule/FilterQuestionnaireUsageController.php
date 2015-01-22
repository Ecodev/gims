<?php

namespace Api\Controller\Rule;

use Api\Controller\AbstractChildRestfulController;
use Zend\View\Model\JsonModel;

class FilterQuestionnaireUsageController extends AbstractChildRestfulController
{

    /**
     * Returns the parent object
     * @return \Application\Model\AbstractModel
     */
    protected function getAllParents()
    {

        if ($this->params('parent')) {
            return [
                $this->params('parent') => $this->params('idParent'),
            ];
        } else {
            return [
                $this->params('parent1') => $this->params('idParent1'),
                $this->params('parent2') => $this->params('idParent2'),
                $this->params('parent3') => $this->params('idParent3'),
            ];
        }
    }

    public function getList()
    {
        $parents = $this->getAllParents();

        if (!$parents) {
            $this->getResponse()->setStatusCode(400);

            return new JsonModel(['message' => 'Cannot list all items without a valid parent. Use URL similar to: /api/parent/1/child']);
        }

        $objects = $this->getRepository()->getAllWithPermission($this->params()->fromQuery('permission', 'read'), $this->params()->fromQuery('q'), $parents);
        $jsonData = $this->paginate($objects);

        return new JsonModel($jsonData);
    }

}
