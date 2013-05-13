<?php

namespace Application\Service;

use \Application\Model\AbstractModel;
use \Zend\Code\Reflection\MethodReflection;
use \Application\Module;

class Hydrator
{

    /**
     * Returns an array of array of property values of all given objects
     *
     * @param array $objects
     * @param array $properties
     *
     * @return array
     */
    public function extractArray($objects, array $properties)
    {
        $result = array();
        foreach ($objects as $object) {
            $result[] = $this->extract($object, $properties);
        }

        return $result;
    }

    /**
     * Return an array of property values of the given object
     *
     * @param AbstractModel $object
     * @param array $properties
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    public function extract(AbstractModel $object, array $properties)
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

                // If method does not exist, skip it
                if (!is_callable(array($object, $getter))) {
                    continue;
                }

                $subObject = $object->$getter();

                // Reuse same configuration if ask for recursivity
                $jsonConfig = $value == '__recursive' ? $properties : $value;
                if ($subObject instanceof \IteratorAggregate) {
                    $propertyValue = $this->extractArray($subObject, $jsonConfig);
                } else {
                    $propertyValue = $subObject ? $this->extract($subObject, $jsonConfig) : null;
                }

                $propertyName = $key;
            } else {
                if (strpos($value, 'is') === 0) {
                    $getter = $value;
                } else {
                    $getter = 'get' . ucfirst($value);
                }

                // If method does not exist, skip it
                if (!is_callable(array($object, $getter))) {
                    continue;
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
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  \Application\Model\AbstractModel $object
     * @return \Application\Model\AbstractModel
     */
    public function hydrate(array $data, \Application\Model\AbstractModel $object)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $getter = 'get' . ucfirst($key);

                if (is_callable(array($object, $getter))) {
                    /** @var $object AbstractModel */
                    $subObject = call_user_func(array($object, $getter));

                    if (is_null($subObject) && !empty($value['id'])) {

                        // Check what kind of parameter type is taken by the setter as input.
                        $modelName = $this->getFirstParameterType($object, $getter);
                        $subObject = $this->getObject($modelName, $value['id']);
                    }

                    // Also hydrate subobject, but only if it's not ourself because it would
                    // overwrite the change we are trying to do. This is typically the case
                    // of user whose last modifier is himself, but could also happen in other cases.
                    if ($object !== $subObject) {
                        $value = $this->hydrate($value, $subObject);
                    } else {
                        $value = $subObject;
                    }
                } else {
                    $logger = Module::getServiceManager()->get('Zend\Log');
                    $logger->info('[WARNING] implement me! Can not persist data. Missing method ' . $getter);

                    // Get or create object from the storage
                    #$modelName = 'Application\Model\Answer' <-- do something better than that
                    #$object = $this->getObject($modelName, $id);
                    #$this->hydrate($data, $object);
                }
            }

            // Assemble setter method
            $setter = 'set' . ucfirst($key);

            // Bonus: code below enabled a short hand "syntax" when assembling a request on the client side.
            // e.g $data[question] = id instead of $data[question] = array('id' => id)
            // Check what kind of parameter type is taken by the setter as input.
            $modelName = $this->getFirstParameterType($object, $setter);

            // If model name is suitable and given $value is numerical, get one from the storage.
            if (is_numeric($value) && preg_match('/Application\\\Model/is', $modelName)) {
                $value = $this->getObject($modelName, $value);
            }

            if (is_callable(array($object, $setter))) {
                call_user_func_array(array($object, $setter), array($value));
            }
        }

        return $object;
    }

    /**
     * Get an object given a Model name and an id
     *
     * @param string $modelName
     * @param int    $id
     *
     * @throws \Exception
     * @return AbstractModel
     */
    protected function getObject($modelName, $id)
    {

        $repository = Module::getEntityManager()->getRepository($modelName);
        $records = $repository->findById($id);

        // raise exception if object does not exist in the DB.
        if (empty($records[0])) {
            $message = sprintf('No object "%s" found for id: %s', $modelName, $id);
            throw new \Exception($message, 1365442789);
        }
        return $records[0];
    }

    /**
     * Get input parameter type for a method (getter or setter)
     *
     * @param AbstractModel $object
     * @param string $methodName
     * @return string type name
     */
    private function getFirstParameterType(AbstractModel $object, $methodName)
    {
        // If the method is a getter transform it into a setter
        // which makes it more straight forward retrieving the type using reflection.
        $methodName = preg_replace('/^get/is', 'set', $methodName);

        if (!method_exists($object, $methodName)) {
            return null;
        }

        $parameterType = null;
        $className = get_class($object);
        $methods = new MethodReflection($className, $methodName);
        foreach ($methods->getParameters() as $parameter) {
            $parameterType = $parameter->getType();
            break; // should be only one parameter in context of setter
        }

        return $parameterType;
    }

}