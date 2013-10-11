<?php

namespace ApplicationTest;

class UtilityTest extends \ApplicationTest\Controller\AbstractController
{

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
        $this->assertSame($expected, \Application\Utility::bcround($number, 3));
    }

    public function testGetCacheKey()
    {
        $foo1 = new \stdClass();
        $foo2 = new \stdClass();
        $foo3 = clone $foo2;

        $allKeys = array();
        $allKeys[] = \Application\Utility::getCacheKey(array());
        $allKeys[] = \Application\Utility::getCacheKey(array('', ''));
        $allKeys[] = \Application\Utility::getCacheKey(array(0));
        $allKeys[] = \Application\Utility::getCacheKey(array(null));
        $allKeys[] = \Application\Utility::getCacheKey(array(null, null));
        $allKeys[] = \Application\Utility::getCacheKey(array(1, 1, null));
        $allKeys[] = \Application\Utility::getCacheKey(array(1, 1));
        $allKeys[] = \Application\Utility::getCacheKey(array(1, 1, ''));
        $allKeys[] = \Application\Utility::getCacheKey(array(1, array(1)));
        $allKeys[] = \Application\Utility::getCacheKey(array('11', 1));
        $allKeys[] = \Application\Utility::getCacheKey(array(1, '11'));
        $allKeys[] = \Application\Utility::getCacheKey(array(1, '11', array(2)));
        $allKeys[] = \Application\Utility::getCacheKey(array(1, '11', array(2, $foo1)));
        $allKeys[] = \Application\Utility::getCacheKey(array(1, '11', array(2, $foo2)));
        $allKeys[] = \Application\Utility::getCacheKey(array(1, '11', array(2, $foo3)));

        $uniqueKeys = array_unique($allKeys);
        $this->assertEquals(count($allKeys), count($uniqueKeys), 'all keys must be unique');

        foreach ($allKeys as $key) {
            $this->assertTrue(is_string($key), 'each key must be a string');
        }
    }

}
