<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

/**
 * This is a controller for objects which are dependent on a parent object.
 * Child objects cannot be listed without specifying a parent.
 *
 * This controller is used with the route /api/subobject
 *
 * Eg: Questions are dependent on the parent Survey, URL would be: /api/survey/1/question
 */
abstract class AbstractChildRestfulController extends AbstractRestfulController
{

    /**
     * Returns the parent object
     * @return \Application\Model\AbstractModel
     */
    protected function getParent()
    {
        $id = $this->params('idParent');
        if ($id) {
            $object = ucfirst($this->params('parent'));
            if ($object == 'Chapter') {
                $object = 'Question\\' . $object;
            }

            if ($object == 'Rule') {
                $object = 'Rule\\' . $object;
            }

            return $this->getEntityManager()->getRepository('Application\Model\\' . $object)->find($id);
        }

        return null;
    }

    public function getList()
    {
        $parent = $this->getParent();

        if (!$parent) {
            $this->getResponse()->setStatusCode(400);

            return new JsonModel(array('message' => 'Cannot list all items without a valid parent. Use URL similar to: /api/parent/1/child'));
        }

        $objects = $this->getRepository()->getAllWithPermission($this->params()->fromQuery('permission', 'read'), $this->params()->fromQuery('q'), $this->params('parent'), $parent);
        $jsonData = $this->paginate($objects);

        return new JsonModel($jsonData);
    }

}
