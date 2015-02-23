<?php

namespace ApiTest\Service;

use Api\Service\MetaModel;

/**
 * @group Service
 */
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
        return [
            [\Application\Model\Question\NumericQuestion::class, ['name', 'survey']],
            [\Application\Model\Survey::class, ['name', 'code']],
                // more models to add here...
        ];
    }
}
