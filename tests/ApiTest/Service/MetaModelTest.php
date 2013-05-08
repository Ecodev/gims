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
     */
    public function methodPropertyExistsReturnsTrueForPropertyIdOfModelSurvey()
    {
        $fixture = new MetaModel();
        $this->assertTrue($fixture->propertyExists('Survey', 'id'));
    }

    /**
     * @test
     */
    public function methodPropertyExistsReturnsFalseForPropertyFooOfModelSurvey()
    {
        $fixture = new MetaModel();
        $this->assertFalse($fixture->propertyExists('Survey', 'foo'));
    }
}
