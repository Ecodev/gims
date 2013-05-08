<?php

namespace ApiTest\Service;


use Api\Service\Permission;

class PermissionTest extends \ApplicationTest\Controller\AbstractController
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function methodIsFieldAllowedReturnsTrueForFieldIdOfModelSurvey()
    {
        $fixture = new Permission('Survey');
        $this->assertTrue($fixture->isFieldAllowed('id'));
    }

    /**
     * @test
     */
    public function methodIsFieldAllowedReturnsFalseForFieldFooOfModelSurvey()
    {
        $fixture = new Permission('Survey');
        $this->assertFalse($fixture->isFieldAllowed('foo'));
    }
}
