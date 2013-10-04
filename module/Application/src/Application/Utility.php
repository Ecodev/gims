<?php

namespace Application;

abstract class Utility
{

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
            if (is_null($arg))
                $key .= '[[NULL]]';
            else if (is_object($arg)) {
                $key .= spl_object_hash($arg);
            }
            else if (is_array($arg))
                $key .= '[[ARRAY|' . self::getCacheKey($arg) . ']]';
            else
                $key .= $arg;

            $key .= '|';
        }

        return $key;
    }

}
