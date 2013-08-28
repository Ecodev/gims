<?php

namespace ApplicationTest;

class NumericJsonModelTest extends \ApplicationTest\Controller\AbstractController
{

    public function numericToStringDataProvider()
    {
        return array(
            array('{"data":0.1}', '{"data":"0.1"}'),
            array('{"data": 0.1}', '{"data": "0.1"}'),
            array('{"data":0.1 }', '{"data":"0.1" }'),
            array('{"data":[0.1]}', '{"data":["0.1"]}'),
            array('{"data":[0.1,0.2]}', '{"data":["0.1","0.2"]}'),
            array('{"style":"border:10px"}', '{"style":"border:10px"}'),
        );
    }

    /**
     * @dataProvider numericToStringDataProvider
     */
    public function testNumericToString($json, $expected)
    {
        $converted = \Application\View\Model\NumericJsonModel::numericToString($json);
        $this->assertSame($expected, $converted, 'numbers should be quoted');

        $convertedBack = \Application\View\Model\NumericJsonModel::stringToNumeric($converted);
        $this->assertSame($json, $convertedBack, 'should be able to convert back to original JSON');
    }

}
