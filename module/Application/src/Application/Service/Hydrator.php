<?php

namespace Application\Service;

use \Application\Model\AbstractModel;
use \Zend\Code\Reflection\MethodReflection;
use \Application\Module;

/**
 * Used to extract object properties into an array or to hydrate an object from an array.
 */
class Hydrator
{

    private $sensitiveProperties = ['password', 'activationToken'];

    /**
     * Returns an array of array of property values of all given objects
     *
     * @param array $objects
     * @param array $properties
     *
     * @return array
     */
    public function extractArray($objects, array $properties = array())
    {
        $properties = $this->initializePropertyStructure($properties);

        return $this->internalExtractArray($objects, $properties);
    }

    /**
     * Returns an array of array of property values of all given objects
     *
     * @param array $objects
     * @param array $properties
     *
     * @return array
     */
    private function internalExtractArray($objects, array $properties = array())
    {
        $result = array();
        foreach ($objects as $object) {
            $result[] = $this->internalExtract($object, $properties);
        }

        return $result;
    }

    /**
     * Replace 'metadata' alias with actual property names.
     * E.g. metadata which is just an alias to dateModified, dateCreated, creator, modifier
     *
     * @param array  $properties
     * @return array
     */
    private function resolveMetadataAliases(array $properties)
    {
        $metadata = array(
            'modifier',
            'creator',
            'dateModified',
            'dateCreated',
        );

        foreach ($properties as $i => $property) {
            if (is_string($property) && preg_match('/^(.*)metadata/', $property, $matches)) {
                $prefix = $matches[1];
                unset($properties[$i]);
                foreach ($metadata as $m) {
                    array_unshift($properties, $prefix . $m);
                }
            }
        }

        return $properties;
    }

    /**
     * Merge with default properties
     *
     * @param AbstractModel $object
     * @param array  $properties
     *
     * @return array
     */
    private function mergeWithDefaultProperties(AbstractModel $object, array $properties)
    {
        $defaultProperties = $object->getJsonConfig();
        $properties = array_merge($defaultProperties, $properties);

        // Avoid duplicate keys.
        // Function "array_unique" can not be used since loop can contains closure.
        $_properties = array();
        foreach ($properties as $key => $property) {
            if (is_string($property) && !in_array($property, $_properties) && $property != '__recursive') {
                $_properties[] = $property;
            } else {
                $_properties[$key] = $property;
            }
        }

        return $_properties;
    }

    /**
     * Convert properties into a real array ready for use in internalExtract()
     *
     * @param array $properties
     * @return array
     */
    public function initializePropertyStructure(array $properties)
    {
        $propertiesWithMetaResolved = $this->resolveMetadataAliases($properties);
        $finalProperties = $this->expandDotsToArray($propertiesWithMetaResolved);

        return $finalProperties;
    }

    /**
     * Return an array of property values of the given object
     *
     * @param AbstractModel $object
     * @param array         $properties
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    public function extract(AbstractModel $object, array $properties = array())
    {
        $properties = $this->initializePropertyStructure($properties);

        return $this->internalExtract($object, $properties);
    }

    /**
     * Return an array of property values of the given object
     *
     * @param AbstractModel $object
     * @param array         $properties
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    private function internalExtract(AbstractModel $object, array $properties = array())
    {
        $result = array();
        $properties = $this->mergeWithDefaultProperties($object, $properties);

        foreach ($properties as $key => $value) {

            // Never output sensitive data
            if (in_array($value, $this->sensitiveProperties)) {
                continue;
            }

            if ($value instanceof \Closure) {
                if (!is_string($key)) {
                    throw new \InvalidArgumentException('Cannot use Closure without a named key.');
                }

                $propertyName = $key;
                $propertyValue = $value($this, $object);
            } elseif (is_string($key)) {
                $getter = $this->formatGetter($key);

                // If method does not exist, skip it
                if (!is_callable(array($object, $getter))) {
                    continue;
                }

                $subObject = $object->$getter();

                // Reuse same configuration if ask for recursive
                $subObjectProperties = $value == '__recursive' ? $properties : $value;
                if ($subObject instanceof \IteratorAggregate) {
                    $propertyValue = $this->internalExtractArray($subObject, $subObjectProperties);
                } else {
                    $propertyValue = $subObject ? $this->internalExtract($subObject, $subObjectProperties) : null;
                }

                $propertyName = $key;
            } else {
                $getter = $this->formatGetter($value);

                // If method does not exist, skip it
                if (!is_callable(array($object, $getter))) {
                    continue;
                }

                $propertyValue = $object->$getter();
                if ($propertyValue instanceof \DateTime) {
                    $propertyValue = $propertyValue->format(\DateTime::ISO8601);
                } elseif ($propertyValue instanceof \IteratorAggregate) {
                    $propertyValue = $this->internalExtractArray($propertyValue);
                } elseif ($propertyValue instanceof \Application\Model\AbstractModel) {
                    $propertyValue = $this->internalExtract($propertyValue);
                } elseif ($propertyValue instanceof \Application\Model\AbstractEnum) {
                    $propertyValue = (string) $propertyValue;
                }

                $propertyName = $value;
            }

            $result[$propertyName] = $propertyValue;
        }

        return $result;
    }

    /**
     * Format a getter and returns it.
     *
     * @param string $input
     *
     * @return string
     */
    private function formatGetter($input)
    {
        // If it is a boolean, do nothing
        if (preg_match('/^is[A-Z]/', $input)) {
            return $input;
        } else {
            return 'get' . ucfirst($input);
        }
    }

    /**
     * Hydrate $object with the provided $data. This is NOT recursive.
     *
     * Supported format for $data is the following:
     *
     * <code>
     * array(
     *      'text' => 'some name',
     *      'number' => 1234,
     *      'date' => '2013-08-05T13:15:57+0900',
     *      'enum' => 'completed',
     *      'subObject' => 3 // Use single ID
     *      'subObjectBis' => array('id' => 3, 'foo' => 'bar') // Use full object, including its ID. the subobject will NOT be hydrated
     *      'subObjects' => array(3, 4, 5) // Use array of single IDs
     *      'subObjectsBis' => array(array('id' => 3), array('id' => 4), array('id' => 5)) // Use array of full objects, including their IDs
     * )
     * </code>
     *
     * @param  array $data
     * @param  \Application\Model\AbstractModel $object
     *
     * @return \Application\Model\AbstractModel
     */
    public function hydrate(array $data, \Application\Model\AbstractModel $object)
    {
        // Remove sensitive data
        foreach ($this->sensitiveProperties as $sensitiveProperty) {
            unset($data[$sensitiveProperty]);
        }

        foreach ($data as $key => $value) {
            // Check what kind of parameter type is taken by the setter as input.
            $setter = 'set' . ucfirst($key);
            $parameterType = $this->getFirstParameterType($object, $setter);

            // If parameter is DateTime, instantiate it
            if ($parameterType == 'DateTime') {
                $value = new \DateTime($value);
            }
            // If model is an AbstractEnum, built it
            elseif (is_subclass_of($parameterType, 'Application\Model\AbstractEnum')) {
                $value = call_user_func_array(array($parameterType, 'get'), array($value));
            }
            // If parameter is an object, get it from database, it can be either an ID, or an array with the key 'id'
            elseif (is_subclass_of($parameterType, 'Application\Model\AbstractModel') && !is_null($value)) {
                $id = is_array($value) ? $value['id'] : $value;
                $value = $this->getObject($parameterType, $id);
            }
            // If parameter is a collection, then build the collection based on $value which must be an array of ID or an array of objects
            elseif ($parameterType == 'Doctrine\Common\Collections\ArrayCollection') {
                $collection = new \Doctrine\Common\Collections\ArrayCollection();
                $modelInCollection = Module::getEntityManager()->getClassMetadata(get_class($object))->getAssociationTargetClass($key);
                foreach ($value as $id) {
                    $id = is_array($id) ? $id['id'] : $id;
                    $collection->add($this->getObject($modelInCollection, $id));
                }

                $value = $collection;
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
        $record = $repository->findOneById($id);

        // raise exception if object does not exist in the DB.
        if (!$record) {
            $message = sprintf('No object "%s" found for id: %s', $modelName, $id);
            throw new \Exception($message, 1365442789);
        }

        return $record;
    }

    /**
     * Get input parameter type for a method (getter or setter)
     *
     * @param AbstractModel $object
     * @param string        $methodName
     *
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

    /**
     * Expands dot notation into array
     * Eg: ['a', 'b.c'] => ['a', ['b' => 'c']]
     * @param array $properties
     */
    private function expandDotsToArray(array $properties)
    {
        $result = array();
        foreach ($properties as $key => $property) {
            if (!is_string($key) && is_string($property)) {
                $keys = explode('.', $property);

                $value = array_pop($keys);
                $arr = &$result;
                while ($key = array_shift($keys)) {

                    // If value already exists, remove it to replace it with an array ([0 => 'a'], becomes ['a' => array()])
                    if (!is_null($arr)) {
                        $existingKey = array_search($key, $arr);
                        if ($existingKey !== false) {
                            unset($arr[$existingKey]);
                        }
                    }

                    $arr = &$arr[$key];
                }

                if ($value == '__recursive') {
                    $arr = $value;
                } else {
                    $arr[] = $value;
                }
            } else {
                $result[$key] = $property;
            }
        }

        return $result;
    }

}
