<?php

namespace Api\Service;

class MetaModel
{

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
     * Tell whether a property exists in a model.
     *
     * @param string $model
     * @param string $property
     * @return bool
     */
    public function propertyExists($model, $property)
    {
        // Prefix the model if the class is not to be found.
        if (!class_exists($model)) {
            $model = '\\Application\\Model\\' . $model;
        }

        $result = property_exists($model, $property);
        if (!$result) {
            $class = new \ReflectionClass($model);
            $result = $class->hasProperty($property);
        }
        return $result;
    }
}
