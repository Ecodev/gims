<?php

namespace ApiTest\Service;

use Api\Service\MetaModel;

class MetaModelTest extends \ApplicationTest\Controller\AbstractController
{

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     * @dataProvider modelProvider
     */
    public function methodGetMandatoryPropertiesReturns($modelName, $mandatoryProperties)
    {
        $fixture = new MetaModel($modelName);
        $this->assertSame($mandatoryProperties, $fixture->getMandatoryProperties());
    }

    /**
     * Provider
     */
    public function modelProvider()
    {
        return array(
            array('Application\Model\Question\NumericQuestion', array('filter', 'sorting', 'name', 'survey')),
            array('Application\Model\Survey', array('name', 'code')),
            // more models to add here...
        );
    }
}
