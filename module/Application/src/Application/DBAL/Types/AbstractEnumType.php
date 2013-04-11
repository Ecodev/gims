<?php

namespace Application\DBAL\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class AbstractEnumType extends Type
{

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $this->getName();
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $className = preg_replace('/Application\\\\DBAL\\\\Types\\\\(.*)Type/', '$1', get_called_class());
        $method = 'Application\Model\\' . $className . '::get';

        return call_user_func($method, $value);
    }

    /**
     * Returns the type name based on actual class name
     * @return string
     */
    public function getName()
    {
        $naming = new \Doctrine\ORM\Mapping\UnderscoreNamingStrategy();
        $className = preg_replace('/Type$/', '', get_called_class());
        $typeName = $naming->classToTableName($className);

        return $typeName;
    }

}
