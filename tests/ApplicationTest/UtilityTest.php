<?php

namespace ApplicationTest;

use Application\Utility;

/**
 * @group Service
 */
class UtilityTest extends \ApplicationTest\Controller\AbstractController
{

    public function pregReplaceUniqueCallbackDataProvider()
    {
        return array(
            array('/a(.)/', '', 0),
            array('/a(.)/', 'a', 0),
            array('/a(.)/', 'ab', 1),
            array('/a(.)/', 'abab', 1),
            array('/a(.)/', 'ababacac', 2),
            array('/a(.)/', 'ababacababac', 2),
            array('/A(\d+)/', 'A1,A11', 2),
        );
    }

    /**
     * @dataProvider pregReplaceUniqueCallbackDataProvider
     */
    public function testpregReplaceUniqueCallback($pattern, $subject, $expectedCount)
    {
        $callback = function ($matches) use (&$count) {
            $count++;

            return '[' . strtoupper($matches[0]) . ']';
        };

        $count = 0;
        $expected = preg_replace_callback($pattern, $callback, $subject);

        $count = 0;
        $actual = Utility::pregReplaceUniqueCallback($pattern, $callback, $subject);

        $this->assertEquals($expected, $actual);
        $this->assertEquals($expectedCount, $count);
    }

    public function bcroundDataProvider()
    {
        return array(
            array(0, '0.000'),
            array(1, '1.000'),
            array('1.0000000', '1.000'),
            array(1.23456, '1.235'),
            array(1.99999, '2.000'),
            array(-0, '0.000'),
            array('-0', '0.000'),
            array(-1, '-1.000'),
            array('-1.0000000', '-1.000'),
            array(-1.23456, '-1.235'),
            array(-1.99999, '-2.000'),
        );
    }

    /**
     * @dataProvider bcroundDataProvider
     */
    public function testBcround($number, $expected)
    {
        $this->assertSame($expected, Utility::bcround($number, 3));
    }

    public function decimalToRoundedPercentDataProvider()
    {
        return array(
            array(null, null),
            array(1, '100.0'),
            array('1.0000000', '100.0'),
            array(1.23456, '123.5'),
            array(1.99999, '200.0'),
            array(-0, '0.0'),
            array('-0', '0.0'),
            array(-1, '-100.0'),
            array('-1.0000000', '-100.0'),
            array(-1.23456, '-123.5'),
            array(-1.99999, '-200.0'),
            array(0.255, '25.5'),
            array(0.9535, '95.4'),
            array('0.9634999999999999', '96.3'), // This is actually an edge case which gives wrong result, but we tolerate this wrong result, because fixing it would only introduce other edge cases
        );
    }

    /**
     * @dataProvider decimalToRoundedPercentDataProvider
     */
    public function testDecimalToRoundedPercent($number, $expected)
    {
        $this->assertSame($expected, Utility::decimalToRoundedPercent($number));
    }

    private function getCommonCacheValues()
    {
        return [
            [],
            [''],
            ['', ''],
            [false],
            [true],
            [0],
            [null],
            [null, null],
            [1, 1, null],
            [1, 1],
            [1, 1, ''],
            [1, array(1)],
            ['11', 1],
            [1, '11'],
            [1, '11', array(2)],
            1,
            [0 => 1],
            [2 => 1],
        ];
    }

    public function testGetVolatileCacheKey()
    {
        $foo1 = new \stdClass();
        $foo2 = new \stdClass();
        $foo3 = clone $foo2;

        $values = $this->getCommonCacheValues();
        $values[] = [1, '11', array(2, $foo1)];
        $values[] = [1, '11', array(2, $foo2)];
        $values[] = [1, '11', array(2, $foo3)];

        $allKeys = array();
        foreach ($values as $value) {
            $allKeys[] = Utility::getVolatileCacheKey($value);
        }

        $uniqueKeys = array_unique($allKeys);
        $this->assertEquals(count($allKeys), count($uniqueKeys), 'all keys must be unique');

        foreach ($allKeys as $key) {
            $this->assertTrue(is_string($key), 'each key must be a string');
        }

        // Get key and immediately destroy objects
        $firstKey = Utility::getVolatileCacheKey(array(new \stdClass()));
        $secondKey = Utility::getVolatileCacheKey(array(new \stdClass()));
        $this->assertNotEquals($secondKey, $firstKey, 'we should never recycle key from garbage collected objects');
    }

    public function testGetPersistentCacheKey()
    {
        $foo1 = $this->getNewModelWithId('Application\Model\Filter');
        $foo2 = $this->getNewModelWithId('Application\Model\Filter');
        $collection = new \Doctrine\Common\Collections\ArrayCollection([$foo1, $foo2, 123]);

        $values = $this->getCommonCacheValues();
        $values[] = [1, '11', [2, $foo1]];
        $values[] = [1, '11', [2, $foo2]];
        $values[] = $collection;

        $allKeys = array();
        foreach ($values as $value) {
            $allKeys[] = Utility::getPersistentCacheKey($value);
        }

        $uniqueKeys = array_unique($allKeys);
        $this->assertEquals(count($allKeys), count($uniqueKeys), 'all keys must be unique');

        foreach ($allKeys as $key) {
            $this->assertTrue(is_string($key), 'each key must be a string');
        }
    }

    public function testGetColor()
    {
        $codes = [74, 75, 76, 78, 900];
        $ratios = [0, 85, 100];

        foreach ($codes as $code) {
            foreach ($ratios as $ratio) {
                $color1 = Utility::getColor($code, $ratio);
                $color2 = Utility::getColor($code, $ratio);
                $this->assertEquals($color1, $color2, 'colors are not identical');
            }
        }
    }

}
