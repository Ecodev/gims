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


    public static function executeCliCommand($command)
    {
        $fullCommand = 'php htdocs/index.php' . $command . ' > /dev/null 2>&1 &';
        exec($fullCommand);
    }

}
