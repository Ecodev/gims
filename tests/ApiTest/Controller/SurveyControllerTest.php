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
        return array('id', 'name', 'code', 'isActive', 'year', 'dateStart', 'dateEnd', 'questions', 'questionnaires');
    }

    protected function getTestedObject()
    {
        return $this->survey;
    }

    public function testCanUpdateSurvey()
    {
        $data = array(
            'name' => $this->survey->getName() . 'foo',
        );

        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['name'], $actual['name']);
    }

    public function testPostANewSurveyAndCheckResponseReturnsIt()
    {
        // Survey
        $data = array(
            'name' => 'new-survey',
            'code' => 100,
            'year' => 2013,
        );

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['name'], $actual['name']);
    }

    public function testPostANewSurveyReturnsStatusCode403ForUserWithRoleAnonymous()
    {
        // Survey
        $data = array(
            'name' => 'new-survey',
            'code' => 100,
            'year' => 2013,
        );

        $this->rbac->setIdentity(null);
        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(403);
    }

    public function testUpdateASurveyAsAnonymousReturnsStatusCode403()
    {
        $expected = $this->survey->getName() . 'foo';
        $data = array(
            'name' => $expected,
        );

        $this->rbac->setIdentity(null);
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(403);
    }

    public function testCanDeleteSurvey()
    {
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(200);
        $this->assertEquals($this->getJsonResponse()['message'], 'Deleted successfully');
    }

    public function testCannotDeleteUnexistingSurvey()
    {
        $this->dispatch('/api/survey/713705', Request::METHOD_DELETE); // smyle, the sun shines :)
        $this->assertResponseStatusCode(404);
    }

}
