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
     * @var array
     */
    protected $metadata = array(
        'dateCreated',
        'dateModified',
        'creator' => array('name'),
        'modifier' => array('name'),
    );

    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Returns mandatory properties for a model which is computed as follows:
     *
     * - nullable=false as property annotation
     * - property must not have a default value
     *
     * @return array
     */
    public function getMandatoryProperties()
    {
        $properties = array();
        $reflectionClass = new \ReflectionClass($this->modelName);
        $defaultValues = $reflectionClass->getDefaultProperties();
        foreach ($reflectionClass->getProperties() as $property) {
            $reflectionProperty = new \ReflectionProperty($this->modelName, $property->getName());

            $doc = $reflectionProperty->getDocComment();
            if (preg_match('/ORM\\\.+nullable=false/isU', $doc, $annotations) && $defaultValues[$property->getName()] === NULL) {
                $properties[] = $property->getName();
            }
        }

        return $properties;
    }

}
