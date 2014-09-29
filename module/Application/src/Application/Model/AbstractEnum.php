<?php

namespace Application\Model;

/**
 * Base class for enum types, allowing for type hinting.
 *
 * Usage for inheriting classes is as follow:
 *
 * <code>
 * function foo(ConcreteEnum $value)
 * {
 *    echo $value;
 * }
 *
 * foo(ConcreteEnum::$VALUE1);
 * </code>
 */
abstract class AbstractEnum
{

    /**
     * @var string
     */
    private $value;

    /**
     * Initialize the class.
     * This MUST be called at the end of each inheriting class declaration.
     */
    public static function initialize()
    {
        // Create instance of this class to replace string in static properties
        $class = get_called_class();
        $ref = new \ReflectionClass($class);
        $statics = $ref->getStaticProperties();
        foreach ($statics as $name => $value) {
            $ref->setStaticPropertyValue($name, new $class($value));
        }
    }

    /**
     * Get all possible values
     * @return array
     */
    public static function getValues()
    {
        $class = get_called_class();
        $ref = new \ReflectionClass($class);

        return $ref->getStaticProperties();
    }

    /**
     * Returns the Enum object corresponding to value given
     * @param string $value
     * @return self
     * @throws \InvalidArgumentException
     */
    public static function get($value)
    {
        foreach (self::getValues() as $enum) {
            if ((string) $enum == $value) {
                return $enum;
            }
        }

        throw new \InvalidArgumentException("'$value' is not a valid value for enum " . get_called_class());
    }

    /**
     * Protected to avoid misuse of this class
     * @param string $value
     */
    protected function __construct($value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return (string) $this->value;
    }

}
