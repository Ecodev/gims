<?php

namespace Api\Service;

class MetaModel
{
    /**
     * @var string
     */
    protected $modelName;

    /**
     * @param string $modelName
     */
    public function __construct($modelName = ''){
        $this->modelName = $modelName;
    }

    /**
     * Returns mandatory properties for a model which is computed as follows:
     *
     * - nullable=false as property annotation
     * - property must not have a default value
     * - it is not the ID
     *
     * @return array
     */
    public function getMandatoryProperties()
    {
        return $this->getMandatoryPropertiesInternal($this->modelName);
    }

    private function getMandatoryPropertiesInternal($modelName)
    {

        $properties = array();
        $reflectionClass = new \ReflectionClass($modelName);
        $defaultValues = $reflectionClass->getDefaultProperties();
        foreach ($reflectionClass->getProperties() as $property) {
            $reflectionProperty = new \ReflectionProperty($modelName, $property->getName());

            $doc = $reflectionProperty->getDocComment();
            if ($property->getName() != 'id' &&  preg_match('/ORM\\\.+nullable=false/isU', $doc, $annotations) && $defaultValues[$property->getName()] === NULL) {
                $properties[] = $property->getName();
            }
        }

        // Also have a look in parent
        $parent = $reflectionClass->getParentClass();
        if ($parent)
        {
            $properties = array_merge($properties, $this->getMandatoryPropertiesInternal($parent->getName()));
        }

        return $properties;
    }

}
