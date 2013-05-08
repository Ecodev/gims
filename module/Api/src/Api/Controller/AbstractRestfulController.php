<?php

namespace Api\Controller;

use Api\Service\MetaModel;
use Api\Service\Permission;
use Application\Traits\EntityManagerAware;
use Zend\View\Model\JsonModel;
use Application\Model\AbstractModel;

abstract class AbstractRestfulController extends \Zend\Mvc\Controller\AbstractRestfulController
{

    use EntityManagerAware;

    /**
     * @var Permission
     */
    protected $permissionService;

    /**
     * @var MetaModel
     */
    protected $metaModelService;


    public function __construct()
    {
        $this->permissionService = new Permission();
        $this->metaModelService = new MetaModel();
    }

    /**
     * Must return an array of properties that will be exposed publicly via JSON.
     * If a property is actually an object itself, it must have a sub-array of properties
     *
     * @return array JSON configuration
     */
    protected function getJsonConfig()
    {

        $result = array();
        $fieldList = $this->params()->fromQuery('fields');
        if (!empty($fieldList)) {
            $fields = explode(',', $fieldList);

            // metadata is just an alias to dateModified, dateCreated
            if (in_array('metadata', $fields)) {
                $key = array_search('metadata', $fields);
                unset($fields[$key]);
                $fields = array_merge($fields, $this->metaModelService->getMetadata());
            }

            // Check if fields is allowed to be printed out.
            foreach ($fields as $key => $val) {
                $fieldName = is_string($key) ? $key : $val;
                if ($this->permissionService->isFieldAllowed($fieldName)) {
                    $result[$key] = $val;
                }
            }
        }

        return $result;
    }

    /**
     * Returns an array of array of property values of all given objects
     *
     * @param array $objects
     * @param array $properties
     *
     * @return array
     */
    protected function arrayOfObjectsToArray($objects, array $properties)
    {
        $result = array();
        foreach ($objects as $object) {
            $result[] = $this->objectToArray($object, $properties);
        }

        return $result;
    }

    /**
     * Return an array of property values of the given object
     *
     * @param \Application\Model\AbstractModel $object
     * @param array                            $properties
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    protected function objectToArray(AbstractModel $object, array $properties)
    {
        // Always output id
        foreach (array('id') as $value) {
            if (!in_array($value, $properties)) {
                array_unshift($properties, $value);
            }
        }

        $result = array();
        foreach ($properties as $key => $value) {

            if ($value instanceof \Closure) {
                if (!is_string($key)) {
                    throw new \InvalidArgumentException('Cannot use Closure without a named key.');
                }

                $propertyName = $key;
                $propertyValue = $value($this, $object);
            } elseif (is_string($key)) {
                $getter = 'get' . ucfirst($key);
                $subObject = $object->$getter();

                // Reuse same configuration if ask for recursivity
                $jsonConfig = $value == '__recursive' ? $properties : $value;
                if ($subObject instanceof \IteratorAggregate) {
                    $propertyValue = $this->arrayOfObjectsToArray($subObject, $jsonConfig);
                } else {
                    $propertyValue = $subObject ? $this->objectToArray($subObject, $jsonConfig) : null;
                }

                $propertyName = $key;
            } else {
                if (strpos($value, 'is') === 0) {
                    $getter = $value;
                } else {
                    $getter = 'get' . ucfirst($value);
                }
                $propertyValue = $object->$getter();
                if ($propertyValue instanceof \DateTime) {
                    $propertyValue = $propertyValue->format(\DateTime::ISO8601);
                }

                $propertyName = $value;
            }

            $result[$propertyName] = $propertyValue;
        }

        return $result;
    }

    /**
     * Returns the Model class name for the current controller
     *
     * @return string for instance "Application\Model\User"
     */
    protected function getModel()
    {
        $class = get_called_class();
        $shortClass = preg_replace('/(.*\\\\)([^\\\\]+)(Controller$)/', '$2', $class);
        return 'Application\Model\\' . $shortClass;
    }

    /**
     * Returns the repository for the current controller
     *
     * @return \Application\Repository\AbstractRepository
     */
    protected function getRepository()
    {
        return $this->getEntityManager()->getRepository($this->getModel());
    }

    /**
     * @param array $data
     *
     * @return mixed|JsonModel
     */
    public function create($data)
    {
        $modelName = $this->getModel();

        /** @var $object AbstractModel */
        $object = new $modelName();
        $object->updateProperties($data, $modelName);


        $this->getEntityManager()->persist($object);
        $this->getEntityManager()->flush();
        if (!$object) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $this->getResponse()->setStatusCode(201);
        return new JsonModel($this->objectToArray($object, $this->getJsonConfig()));
    }

    /**
     * @param int $id
     *
     * @return mixed|JsonModel
     */
    public function delete($id)
    {
        $object = $this->getRepository()->findOneBy(array('id' => $id));
        if (!$object) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $this->getEntityManager()->remove($object);
        $this->getEntityManager()->flush();
        $this->getResponse()->setStatusCode(200);
        return new JsonModel(array('message' => 'deleted successfully'));
    }

    /**
     * @param int $id
     *
     * @return mixed|JsonModel
     */
    public function get($id)
    {
        $object = $this->getRepository()->findOneBy(array('id' => $id));
        if (!$object) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        return new JsonModel($this->objectToArray($object, $this->getJsonConfig()));
    }

    /**
     * @return mixed|JsonModel
     */
    public function getList()
    {
        $objects = $this->getRepository()->findAll();

        return new JsonModel($this->arrayOfObjectsToArray($objects, $this->getJsonConfig()));
    }

    /**
     * @param int   $id
     * @param array $data
     *
     * @return mixed|JsonModel
     */
    public function update($id, $data)
    {
        /** @var $object AbstractModel */
        $object = $this->getRepository()->findOneBy(array('id' => $id));

        if (!$object) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $object->updateProperties($data);
        $this->getEntityManager()->flush();

        $this->getResponse()->setStatusCode(201);
        return new JsonModel($this->objectToArray($object, $this->getJsonConfig()));
    }

}
