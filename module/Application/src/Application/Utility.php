<?php

namespace Application;

use \MischiefCollective\ColorJizz\Formats\HSV;

abstract class Utility
{

    /**
     * Convert a percentage expressed between 0.00-1.00 to a string representation
     * of the percentage between 0-100 (rounded to 1 decimal)
     * Eg: 0.9634999999999999 => '96.34'
     * @param float|null $decimal
     * @return string|null
     */
    public static function decimalToRoundedPercent($decimal)
    {
        if (is_null($decimal)) {
            return null;
        } else {
            return bcmul(self::bcround($decimal, 3), 100, 1);
        }
    }

    /**
     * Find an object by id on assoc array
     * @param $id
     * @param $objects
     * @return null
     */
    public static function getObjectById($id, $objects)
    {

        if ($id) {
            foreach ($objects as $o) {
                if ($o['id'] == $id) {
                    return $o;
                }
            }
        }

        return null;
    }

    /**
     * This round a number with arbitrary precision
     *
     * Once rounded, the number *MUST NOT* be converted to float anymore.
     * See http://www.php.net/manual/en/book.bc.php
     * @param float $number
     * @param integer $precision
     * @return string representation of the rounded number
     */
    public static function bcround($number, $precision = 0)
    {
        $sign = $number >= 0 ? '' : '-';
        $zeros = str_repeat('0', $precision);

        return bcadd($number, $sign . "0.{$zeros}5", $precision);
    }

    /**
     * Execute a GIMS command via CLI
     * @param string $command
     */
    public static function executeCliCommand($command)
    {
        $fullCommand = 'php htdocs/index.php ' . $command . ' > /dev/null 2>&1 &';
        exec($fullCommand);
    }

    /**
     * Returns a unique key identifying all arguments in the array, so we can use the result as cache key
     * @param array $args
     * @return string
     */
    public static function getCacheKey(array $args)
    {
        $key = '';
        foreach ($args as $arg) {
            if (is_null($arg)) {
                $key .= '[[NULL]]';
            } elseif (is_object($arg)) {
                $key .= spl_object_hash($arg);
            } elseif (is_array($arg)) {
                $key .= '[[ARRAY|' . self::getCacheKey($arg) . ']]';
            } elseif (is_bool($arg)) {
                $key .= '[[BOOL|' . $arg . ']]';
            } else {
                $key .= $arg;
            }

            $key .= '|';
        }

        return $key;
    }

    /**
     * Same as preg_replace_callback(), but call the callback only once per unique match
     * @param string $pattern
     * @param \Closure $callback
     * @param string $subject
     * @return string replaced string
     */
    public static function pregReplaceUniqueCallback($pattern, \Closure $callback, $subject)
    {
        $replacements = array();

        return preg_replace_callback($pattern, function($matches) use ($callback, &$replacements) {
            $key = $matches[0];

            if (!isset($replacements[$key])) {
                $replacement = $callback($matches);
                $replacements[$key] = $replacement;
            }

            return $replacements[$key];
        }, $subject);
    }

    /**
     * Generate a color from a number and a ratio
     *
     * @param $number
     * @param $ratio the color saturation from 0 to 100
     *
     * @return string
     */
    public static function getColor($number, $ratio)
    {
        // multiply number by phi (golden number constant) to ensure the number is between 0 and 1
        $phi = (1 + sqrt(5)) / 2;
        $number = $number * $phi - floor($number * $phi);
        $number *= 360; // tsl/hsv tint is between 0° and 360°

        $hsv = new HSV($number, $ratio, 85);
        $rgb = $hsv->toRGB();
        $hex = $rgb->toHex();

        return '#' . $hex;
    }

    /**
     * Returns the short class name of any object, eg: Application\Model\Survey => Survey
     * @param object $object
     * @return string
     */
    public static function getShortClassName($object)
    {
        $reflect = new \ReflectionClass($object);

        return $reflect->getShortName();
    }

    /**
     * Returns a string of the object list
     * @param array|\Traversable $objects
     * @return string
     */
    public static function objectsToString($objects)
    {
        $names = [];
        foreach ($objects as $object) {
            $id = $object->getId() ? '#' . $object->getId() : '#null';
            $names[] = '' . self::getShortClassName($object) . $id . ' (' . $object->getName() . ')';
        }

        return implode(', ', $names);
    }

}
