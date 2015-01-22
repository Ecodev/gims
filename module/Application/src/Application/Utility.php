<?php

namespace Application;

use MischiefCollective\ColorJizz\Formats\Hex;
use MischiefCollective\ColorJizz\Formats\HSV;

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
     * @param integer $id
     * @param array $objects
     * @return null
     */
    public static function getObjectById($id, array $objects)
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
     * Return a new array indexed by the objects ID
     * @param Model\AbstractModel[] $objects
     * @return Model\AbstractModel[] $objects indexed by their ID
     */
    public static function indexById(array $objects)
    {
        $result = [];
        foreach ($objects as $object) {
            $result[$object->getId()] = $object;
        }

        return $result;
    }

    /**
     * Re-order objects to be the same order as given ID
     * @param \Application\Model\AbstractModel[] $objects
     * @param integer[] $ids
     * @return Model\AbstractModel[] $objects sorted the same as given IDs
     */
    public static function orderByIds(array $objects, array $ids)
    {
        usort($objects, function ($o1, $o2) use ($ids) {
            return array_search($o1->getId(), $ids) > array_search($o2->getId(), $ids);
        });

        return $objects;
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
     * Execute a GIMS command via CLI asynchronously
     * @param array $args any number of arguments
     */
    public static function executeCliCommand(/* ...$args */)
    {
        $args = func_get_args();

        $escapedArguments = array_reduce($args, function ($result, $arg) {
            return $result . ' ' . escapeshellarg($arg);
        });

        $fullCommand = 'php htdocs/index.php ' . $escapedArguments . ' > /dev/null 2>&1 &';
        _log()->debug(__METHOD__, [$fullCommand]);
        exec($fullCommand);
    }

    /**
     * Returns a unique key identifying all arguments in the array, so we can use the result as cache key
     *
     * In most case you should use getPersistentCacheKey() instead of this method.
     *
     * This only works for in-memory objects. The key returned should *never* be
     * persisted. And it is quite expensive in memory because object are forced
     * not to be garbage collected.
     * @param mixed $value
     * @return string
     */
    public static function getVolatileCacheKey($value)
    {
        return self::getCacheKey($value, false);
    }

    /**
     * Returns a unique key identifying all arguments in the array, so we can use the result as cache key
     * This only works for persisted Model objects (with an ID) and collection
     * of Model objects. The returned key can be persisted.
     * @param mixed $value
     * @return string
     */
    public static function getPersistentCacheKey($value)
    {
        return self::getCacheKey($value, true);
    }

    /**
     * Returns a unique key
     * @param mixed $value
     * @param bool $isPersistent wether to use the persistent variant
     * @return string
     */
    private static function getCacheKey($value, $isPersistent)
    {
        static $preventGarbageCollectorFromDestroyingObject = [];

        $key = '';
        if (is_array($value)) {
            $key .= '[ARRAY|';
            foreach ($value as $i => $modelInCollection) {
                $key .= $i . '>' . self::getCacheKey($modelInCollection, $isPersistent) . ':';
            }
            $key .= ']';
        } elseif (is_object($value)) {
            if ($isPersistent) {
                if ($value instanceof \Traversable) {
                    $key .= '[COLLECTION|';
                    foreach ($value as $modelInCollection) {
                        $key .= $modelInCollection->getId() . ':';
                    }
                    $key .= ']';
                } else {
                    $key .= $value->getId();
                }
            } else {
                $preventGarbageCollectorFromDestroyingObject[] = $value;
                $key .= spl_object_hash($value);
            }
        } elseif (is_bool($value)) {
            $key .= '[BOOL|' . $value . ']';
        } elseif (is_null($value)) {
            $key .= '[NULL]';
        } else {
            $key .= $value;
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
        $replacements = [];

        return preg_replace_callback($pattern, function ($matches) use ($callback, &$replacements) {
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

    public static function getLisibleColor($color)
    {
        $color = str_replace('#', '', $color);

        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));

        $brightness = sqrt($r * $r * 0.241 + $g * $g * 0.691 + $b * $b * 0.068);

        if ($brightness < 150) {
            $textColor = '#FFFFFF';
        } else {
            $textColor = '#000000';
        }

        return $textColor;
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

    /**
     * Explode a string by commas into an array of ID
     * @param string|null $ids
     * @return array
     */
    public static function explodeIds($ids)
    {
        if (!$ids) {
            return [];
        }

        return preg_split('/,/', trim($ids), -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * Return Gravatar URL
     * @param string $email
     * @return string Gravatar URL
     */
    public static function getGravatar($email)
    {
        return 'https://secure.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=identicon';
    }

}
