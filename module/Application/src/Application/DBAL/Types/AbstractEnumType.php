<?php

namespace Application\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class AbstractEnumType extends Type
{
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        $possibleValues = $this->getPossibleValues();
        $quotedPossibleValues = implode(',', array_map(function ($str) {
                    return "'" . (string) $str . "'";
                }, $possibleValues));

        $sql = "ENUM(" . $quotedPossibleValues . ")";

        return $sql;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $className = $this->getClassName();

        return call_user_func([$className, 'get'], $value);
    }

    private function getPossibleValues()
    {
        $className = $this->getClassName();

        return call_user_func([$className, 'getValues']);
    }

    private function getClassName()
    {
        $shortClassName = preg_replace('/Application\\\\DBAL\\\\Types\\\\(.*)Type/', '$1', get_called_class());

        return 'Application\Model\\' . $shortClassName;
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

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }
}
