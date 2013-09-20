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
            array('{"123":456}', '{"123":"456"}'), // numeric keys should not be transformed to numbers
            array('{"id":"187:19"}', '{"id":"187:19"}'), // number in quotes should not be quoted
            array('{"id":"18\"7:19"}', '{"id":"18\"7:19"}'), // escaped quote within string should not break anything
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
