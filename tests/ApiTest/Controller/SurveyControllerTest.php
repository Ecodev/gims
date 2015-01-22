<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

/**
 * @group Rest
 */
class SurveyControllerTest extends AbstractRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return ['id', 'name', 'code', 'isActive', 'year', 'dateStart', 'dateEnd', 'questions', 'questionnaires'];
    }

    protected function getTestedObject()
    {
        return $this->survey;
    }

    public function testCanUpdateSurvey()
    {
        $data = [
            'name' => $this->survey->getName() . 'foo',
        ];

        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['name'], $actual['name']);

        // Same with anonymous will fail
        $this->identityProvider->setIdentity(null);
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(403);
    }

    public function testPostANewSurveyAndCheckResponseReturnsIt()
    {
        // Survey
        $data = [
            'name' => 'new-survey',
            'code' => 100,
            'year' => 2013,
        ];

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['name'], $actual['name']);

        // Same with anonymous will fail
        $this->identityProvider->setIdentity(null);
        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(403);
    }

}
