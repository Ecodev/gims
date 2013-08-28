<?php

namespace Application\View\Model;

use Zend\View\Model\JsonModel;

/**
 * This JSON model will automatically convert numeric values found in string to
 * real numeric values. This is especially useful when working with BC Math Functions
 * for arbitrary precision mathematics. So we can keep working with strings, until
 * the final conversion to JSON.
 */
class NumericJsonModel extends JsonModel
{

    /**
     * Convert a JSON string containing numeric values as string to a JSON
     * containing numeric values as float
     *
     * @param string $json
     * @return string
     */
    public static function stringToNumeric($json)
    {
        return preg_replace('/"(-?\d+\.?\d*)"/', '$1', $json);
    }

    /**
     * Convert a JSON string containing numeric values as float to a JSON
     * containing numeric values as string
     *
     * @param string $json
     * @return string
     */
    public static function numericToString($json)
    {
        return preg_replace('/:\s*(-?\d+\.?\d*)\b/', ':"$1"', $json);
    }

    /**
     * Serialize to JSON
     *
     * @return string
     */
    public function serialize()
    {
        $json = parent::serialize();

        // Here we cannot use JSON_NUMERIC_CHECK, because it will convert our
        // string into a float representation and lose its accuracy, so we keep
        // everything as strings, and modify the final string
        return self::stringToNumeric($json);
    }

}
