<?php

namespace ApiTest\Controller;

use Application\Model\Survey;
use Zend\Http\Request;

class SurveyControllerTest extends AbstractController
{

    /**
     * Get suitable route for GET method.
     *
     * @param string $method
     *
     * @return string
     */
    private function getRoute($method)
    {
        switch ($method) {
            case 'delete':
            case 'get':
                $route = sprintf(
                        '/api/survey/%s', $this->survey->getId()
                );
                break;
            case 'post':
                $route = '/api/survey';
                break;
            case 'put':
                $route = sprintf(
                        '/api/survey/%s?id=%s', $this->survey->getId(), $this->survey->getId()
                );
                break;
            default:
                $route = '';
        }

        return $route;
    }

    /**
     * @test
     * @group SurveyApi
     */
    public function dispatchRouteForSurveyReturnsStatus200()
    {
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $this->assertResponseStatusCode(200);
    }

    /**
     * @test
     * @group SurveyApi
     */
    public function ensureOnlyAllowedFieldAreDisplayedInResponseForSurvey()
    {
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $allowedFields = array('id', 'name', 'code', 'isActive', 'year', 'dateStart', 'dateEnd', 'questions', 'questionnaires');
        foreach ($this->getJsonResponse() as $key => $value) {
            $this->assertTrue(in_array($key, $allowedFields));
        }
    }

    /**
     * @test
     * @group SurveyApi
     */
    public function getSurveyAndCheckWhetherIdFromResponseIsCorrespondingToFakeSurvey()
    {
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $actual = $this->getJsonResponse();
        $this->assertSame($this->survey->getId(), $actual['id']);
    }

    /**
     * @test
     * @group SurveyApi
     */
    public function getSurveyWithUnknownFieldsAreIgnored()
    {
        $this->dispatch($this->getRoute('get') . '?fields=foo', Request::METHOD_GET);
        $actual = $this->getJsonResponse();
        $this->assertArrayNotHasKey('foo', $actual);
    }

    /**
     * @test
     * @group SurveyApi
     */
    public function getSurveyWithFieldsParametersEqualsToMetadataAndCheckWhetherResponseContainsMetadataFields()
    {
        $this->dispatch($this->getRoute('get') . '?fields=metadata', Request::METHOD_GET);
        $actual = $this->getJsonResponse();
        foreach (Survey::getMetadata() as $key => $val) {
            $metadata = is_string($key) ? $key : $val;
            $this->assertArrayHasKey($metadata, $actual);
        }
    }

    /**
     * @test
     * @group SurveyApi
     */
    public function getSurveyWithFieldsParametersEqualsToDateCreatedAndCheckWhetherResponseContainsField()
    {
        $expected = 'dateCreated';
        $this->dispatch($this->getRoute('get') . '?fields=' . $expected, Request::METHOD_GET);
        $actual = $this->getJsonResponse();
        $this->assertArrayHasKey($expected, $actual);
    }

    /**
     * @test
     * @group SurveyApi
     */
    public function updateNameOfSurveyAndCheckWhetherOriginalNameIsDifferentFromUpdatedValue()
    {
        $this->rbac->setIdentity($this->user);

        $expected = $this->survey->getName();
        $data = array(
            'name' => $this->survey->getName() . 'foo',
        );

        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $actual = $this->getJsonResponse();
        $this->assertNotEquals($expected, $actual['name']);
    }

    /**
     * @test
     * @group SurveyApi
     */
    public function updateAnSurveyWillReturn201AsCode()
    {
        $this->rbac->setIdentity($this->user);

        $expected = $this->survey->getName() . 'foo';
        $data = array(
            'name' => $expected,
        );

        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
    }

    /**
     * @test
     * @group SurveyApi
     */
    public function postANewSurveyAndCheckResponseReturnsIt()
    {
        $this->rbac->setIdentity($this->user);

        // Survey
        $data = array(
            'name' => 'new-survey',
            'code' => 100,
            'year' => 2013,
        );

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['name'], $actual['name']);
    }

    /**
     * @test
     * @group SurveyApi
     */
    public function postANewSurveyReturnsStatusCode401ForUserWithRoleAnonymous()
    {
        // Question
        $data = array(
            'name' => 0.6,
            'question' => array(
                'id' => $this->question->getId()
            ),
            'questionnaire' => array(
                'id' => $this->questionnaire->getId()
            ),
            'part' => array(
                'id' => $this->part->getId()
            ),
        );

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        // @todo comment me out once permission will be enabled (=> GUI handling)
        #$this->assertResponseStatusCode(401);
    }

    /**
     * @test
     * @group SurveyApi
     */
    public function postANewSurveyReturnsStatusCode201ForUserWithRoleReporter()
    {
        $this->rbac->setIdentity($this->user);
        // Question
        $data = array(
            'name' => 'new-survey',
            'code' => 100,
            'year' => 2013,
        );

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(201);
    }

    /**
     * @test
     * @group SurveyApi
     */
    public function updateAnSurveyAsAnonymousReturnsStatusCode401()
    {
        $expected = $this->survey->getName() . 'foo';
        $data = array(
            'name' => $expected,
        );

        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        // @todo comment me out once permission will be enabled (=> GUI handling)
        #$this->assertResponseStatusCode(401);
    }

    /**
     * @test
     * @group SurveyApi
     */
    public function updateAnSurveyWithRoleReporterReturnsStatusCode201()
    {
        $this->rbac->setIdentity($this->user);
        $expected = $this->survey->getName() . 'foo';
        $data = array(
            'name' => $expected,
        );

        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
    }

    /**
     * @test
     * @group SurveyApi
     */
    public function deleteSurveyMustReturnStatusCode200()
    {
        $this->rbac->setIdentity($this->user);
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(200);
    }

    /**
     * @test
     * @group SurveyApi
     */
    public function deleteSurveyMustContainsMessageDeletedSuccessfully()
    {
        $this->rbac->setIdentity($this->user);
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertEquals($this->getJsonResponse()['message'], 'Deleted successfully');
    }

    /**
     * @test
     * @group SurveyApi
     */
    public function deleteASurveyWhichDoesNotExistReturnsStatusCode404()
    {
        $this->rbac->setIdentity($this->user);
        $this->dispatch('/api/survey/' . ($this->survey->getId() + 1), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(404);
    }

}
