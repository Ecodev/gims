<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;
use Application\Model\AbstractModel;

abstract class AbstractRestfulController extends \Zend\Mvc\Controller\AbstractRestfulController
{

    use \Application\Traits\EntityManagerAware;

    /**
     * Must return an array of properties that will be exposed publicly via JSON.
     * If a property is actually an object itself, it must have a sub-array of properties
     * @return array JSON configuration
     */
    protected abstract function getJsonConfig();

    /**
     * Returns an array of array of property values of all given objects
     * @param array $objects
     * @param array $properties
     * @return array
     */
    protected function arrayOfObjectsToArray(array $objects, array $properties)
    {
        $result = array();
        foreach ($objects as $object) {
            $result[] = $this->objectToArray($object, $properties);
        }

        return $result;
    }

    /**
     * Return an array of property values of the given object
     * @param \Application\Model\AbstractModel $object
     * @param array $properties
     * @return array
     */
    protected function objectToArray(AbstractModel $object, array $properties)
    {
        // Always output id
        array_unshift($properties, 'id');

        $result = array();
        foreach ($properties as $key => $value) {

            if (is_string($key)) {
                $getter = 'get' . ucfirst($key);
                $subObject = $object->$getter();
                $result[$key] = $this->objectToArray($subObject, $value);
            } else {
                $getter = 'get' . ucfirst($value);
                $scalarObject = $object->$getter();
                if ($scalarObject instanceof \DateTime)
                    $scalarObject = $scalarObject->format(\DateTime::ISO8601);
                $result[$value] = $scalarObject;
            }
        }

        return $result;
    }

    /**
     * Returns the repository for the current controller
     * @return \Application\Repository\AbstractRepository
     */
    protected function getRepository()
    {
        $class = get_called_class();
        $shortClass = preg_replace('/(.*\\\\)([^\\\\]+)(Controller$)/', '$2', $class);
        $modelClass = 'Application\Model\\' . $shortClass;

        return $this->getEntityManager()->getRepository($modelClass);
    }

    public function create($data)
    {
        // TODO: do something clever ...
    }

    public function delete($id)
    {
        $object = $this->getRepository()->findOneBy(array('id' => $id));
        if (!$object) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        $this->getEntityManager()->remove($object);
        $this->getEntityManager()->flush();
        
        return new JsonModel(array('message' => 'deleted successfully'));
    }

    public function get($id)
    {
        $object = $this->getRepository()->findOneBy(array('id' => $id));
        if (!$object) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        return new JsonModel($this->objectToArray($object, $this->getJsonConfig()));
    }

    public function getList()
    {
        $objects = $this->getRepository()->findAll();

        return new JsonModel($this->arrayOfObjectsToArray($objects, $this->getJsonConfig()));
    }

    public function update($id, $data)
    {
        $object = $this->getRepository()->findOneBy(array('id' => $id));
        if (!$object) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        
        // TODO: do something clever ...
        
        
        return new JsonModel($this->objectToArray($object, $this->getJsonConfig()));
    }

}
