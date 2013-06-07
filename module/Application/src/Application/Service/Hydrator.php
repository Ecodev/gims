<?php

namespace Application\Service;

use \Application\Model\AbstractModel;
use \Zend\Code\Reflection\MethodReflection;
use \Application\Module;

class Hydrator
{

    /**
     * @var array
     */
    protected $propertyStructure = array();

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
     * Return properties from the property structure given an entity.
     *
     * @param string $className
     *
     * @throws \Exception
     * @return array
     */
    public function getJsonConfigForEntity($className)
    {

        // Launch exception if value is still missing
        if (empty($this->propertyStructure[$className])) {
            throw new \Exception('Not existing className key in property structure ' . $className, 1370277983);
        }

        return $this->propertyStructure[$className];
    }

    /**
     * Parse given properties and fill-in attribute "propertyStructure"
     *
     * @param string $className
     * @param array  $properties
     *
     * @return void
     */
    public function parseProperties($className, array $properties)
    {
        foreach ($properties as $key => $property) {
            if (is_string($property) && is_int(strpos($property, '.'))) {

                $structure = explode('.', $property);

                // remove last segment as key for the array
                $element = array_shift($structure);

                // Initialize a new structure, the first element without
                $subStructure = array(
                    implode('.', $structure)
                );
                $subClassName = call_user_func_array($className . '::getRelation', array($element));
                $this->parseProperties($subClassName, $subStructure);
            } elseif ($property instanceof \Closure) {
                $this->propertyStructure[$className][$key] = $property;
            } else {
                $this->propertyStructure[$className][] = $property;

                // check whether the property returns a collection
                // for performance reasons don't use reflection
                if (call_user_func_array($className . '::hasRelation', array($property))) {
                    $relationClassName = call_user_func_array($className . '::getRelation', array($property));
                    if (!isset($this->propertyStructure[$relationClassName])) {
                        $this->propertyStructure[$relationClassName] = array();
                    }
                }
            }
        }
    }

    /**
     * Complete property structure with default properties
     *
     * @return void
     */
    public function completePropertyStructureWithDefaultProperties()
    {
        foreach ($this->getPropertyStructure() as $className => $properties) {

            // Merge with default properties
            $this->propertyStructure[$className] = array_merge(
                $properties, call_user_func($className . '::getJsonConfig')
            );
        }
    }

    /**
     * Check that properties from the structure are allowed to be outputted.
     *
     * @return void
     */
    public function checkPropertyPermission()
    {
        // Check if fields is allowed to be printed out.
        foreach ($this->getPropertyStructure() as $className => $properties) {

            $_properties = array();
            foreach ($properties as $key => $property) {

                $isAllowed = false;

                // If method does not exist, skip it
                // Check if the property is callable or is defined as default
                if ((is_string($property) && is_callable(array($className, $this->formatGetter($property))))
                    || call_user_func_array($className . '::isPropertyInJsonConfig', array($property))
                ) {
                    $isAllowed = true;
                }

                // @todo implement me!
                // @todo encapsulate routine above into isFieldAllowed
                //if ($this->permissionService->isFieldAllowed($properties))

                // @todo remove me probably. Move closure as property of model?
                if ($property instanceof \Closure) {
                    $_properties[$key] = $property;
                } elseif ($isAllowed) {
                    $_properties[] = $property;
                }
            }

            // Merge with default properties
            $this->propertyStructure[$className] = $_properties;
        }
    }

    /**
     * Resolve property aliases.
     * E.g. metadata which is just an alias to dateModified, dateCreated, creator, modifier
     *
     * @param string $className
     * @param array  $properties
     *
     * @return array
     */
    public function resolvePropertyAliases($className, array $properties) {

        $_properties = array();
        foreach ($properties as $key => $property) {
            if (is_string($key)) { // most probably a closure
                $_properties[$key] = $property;
            } elseif (preg_match('/metadata/is', $property)) {
                foreach (call_user_func($className . '::getMetadata') as $metadata) {
                    $_properties[] = str_replace('metadata', $metadata, $property);
                }
            } else {
                $_properties[] = $property;
            }
        }

        return $_properties;
    }

    /**
     * Merge with default properties
     *
     * @param string $className
     * @param array  $properties
     *
     * @return array
     */
    public function mergeWithDefaultProperties($className, array $properties)
    {
        $defaultProperties = call_user_func($className . '::getJsonConfig');
        $properties = array_merge($defaultProperties, $properties);

        // Avoid duplicate keys.
        // Function "array_unique" can not be used since loop can contains closure.
        $_properties = array();
        foreach ($properties as $key => $property) {
            if (is_string($property) && !in_array($property, $_properties)) {
                $_properties[] = $property;
            } else {
                $_properties[$key] = $property;
            }
        }
        return $_properties;
    }


    /**
     * Main method for initializing attribute "propertyStructure"
     *
     * @param string $className
     * @param array  $properties
     *
     * @return array
     */
    public function initializePropertyStructure($className, array $properties)
    {
        // instantiate property with default value
        $this->propertyStructure[$className] = array();

        $properties = $this->mergeWithDefaultProperties($className, $properties);
        $properties = $this->resolvePropertyAliases($className, $properties);

        // Analyse properties and build a property structure.
        $this->parseProperties($className, $properties);

        $this->completePropertyStructureWithDefaultProperties();
        $this->checkPropertyPermission();
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
    public function extract(AbstractModel $object, array $properties)
    {
        $result = array();

        $this->initializePropertyStructure($this->getClassName($object), $properties);
        $properties = $this->getJsonConfigForEntity($this->getClassName($object));

        foreach ($properties as $key => $value) {

            // Never output sensitive data
            if ($value == 'password') {
                continue;
            }

            if ($value instanceof \Closure) {
                if (!is_string($key)) {
                    throw new \InvalidArgumentException('Cannot use Closure without a named key.');
                }

                $propertyName = $key;
                $propertyValue = $value($this, $object);
            } elseif (is_string($key)) {
                $getter = $this->formatGetter($value);

                // If method does not exist, skip it
                if (!is_callable(array($object, $getter))) {
                    continue;
                }

                $subObject = $object->$getter();

                // Reuse same configuration if ask for recursive
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
                    $getter = $this->formatGetter($value);
                }

                // If method does not exist, skip it
                if (!is_callable(array($object, $getter))) {
                    continue;
                }

                $propertyValue = $object->$getter();
                if ($propertyValue instanceof \DateTime) {
                    $propertyValue = $propertyValue->format(\DateTime::ISO8601);
                } elseif ($propertyValue instanceof \Doctrine\Common\Collections\ArrayCollection
                    || $propertyValue instanceof \Doctrine\ORM\PersistentCollection)
                {

                    $className = call_user_func_array($this->getClassName($object) . '::getRelation', array($value));
                    $_properties = $this->getJsonConfigForEntity($className);
                    $propertyValue = $this->extractArray($propertyValue, $_properties);
                } elseif ($propertyValue instanceof \Application\Model\AbstractModel) {
                    $className = call_user_func_array($this->getClassName($object) . '::getRelation', array($value));
                    $_properties = $this->getJsonConfigForEntity($className);
                    $propertyValue = $this->extract($propertyValue, $_properties);
                } elseif ($propertyValue instanceof \Application\Model\AbstractEnum) {
                    $propertyValue = (string)$propertyValue;
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
    public function formatGetter($input)
    {
        return 'get' . ucfirst($input);
    }

    /**
     * Return a canonical class name given an object
     *
     * @param object $object
     *
     * @return string
     */
    public function getClassName($object)
    {
        return '\\' . ltrim(get_class($object), '\\');
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  \Application\Model\AbstractModel $object
     *
     * @return \Application\Model\AbstractModel
     */
    public function hydrate(array $data, \Application\Model\AbstractModel $object)
    {
        // Remove sensitive data
        unset($data['password']);

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
            } // If model is Date time, instantiate it
            elseif ($modelName == 'DateTime') {
                $value = new \DateTime($value);
            } // If model is an AbstractEnum, built it
            elseif (is_subclass_of($modelName, '\Application\Model\AbstractEnum')) {
                $value = call_user_func_array(array($modelName, 'get'), array($value));
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
     * @return array
     */
    public function getPropertyStructure()
    {
        return $this->propertyStructure;
    }
}