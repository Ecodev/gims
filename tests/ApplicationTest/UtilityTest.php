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

}
