<?php

namespace ApiTest\Controller;

use Application\Model\Geoname;
use Zend\Http\Request;

class QuestionnaireControllerTest extends AbstractController
{

    public function testEnsureOnlyAllowedFieldAreDisplayedInResponseForQuestionnaire()
    {
        $this->dispatch('/api/questionnaire/' . $this->questionnaire->getId(), Request::METHOD_GET);
        $allowedFields = array('id', 'dateObservationStart', 'dateObservationEnd', 'survey', 'name', 'geoname', 'completed', 'spatial', 'dateLastAnswerModification', 'reporterNames', 'validatorNames', 'comments', 'status', 'permission');
        foreach ($this->getJsonResponse() as $key => $value) {
            $this->assertTrue(in_array($key, $allowedFields), sprintf('Field "%s" is not declared as allowed', $key));
        }
    }

    public function testCanListQuestionnaire()
    {
        $this->dispatch($this->getRoute('getList'), Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $json = $this->getJsonResponse();

        // In the array of all questionnaires, we should at least found the test questionnaire
        foreach ($json as $questionnaire) {
            $this->assertGreaterThan(0, $questionnaire['id']);
        }
    }

    public function testCanGetQuestionnaire()
    {
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $json = $this->getJsonResponse();
        $this->assertSame($this->questionnaire->getId(), $json['id']);
    }

    /**
     * @test
     */
    public function questionnaireWithNoAnswerCanBeDeleted()
    {

        // Questionnaire
        $data = array(
            'dateObservationStart' => '2013-05-22T00:00:00.000Z',
            'dateObservationEnd'   => '2014-05-22T00:00:00.000Z',
            'geoname'              => $this->geoName->getId(),
            'survey'               => $this->survey->getId(),
        );

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $actual = $this->getJsonResponse();

        // Should be able to delete once
        $this->dispatch('/api/questionnaire/' . $actual['id'], Request::METHOD_DELETE);
        $this->assertResponseStatusCode(200);
        $this->getJsonResponse();
        $this->assertJsonStringEqualsJsonString(
            '{"message":"deleted successfully"}', $this->getResponse()->getContent()
        );

        // Should not be able to delete the same resource again
        $this->dispatch('/api/questionnaire/' . $actual['id'], Request::METHOD_DELETE);
        $this->assertResponseStatusCode(404);
    }

    /**
     * @test
     */
    public function questionnaireWithExistingAnswersCanNotBeDeleted()
    {
        // Should be able to delete once
        // @todo enable me once we have permission handling
        #$this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        #$this->assertResponseStatusCode(403);
    }

    /**
     * @test
     */
    public function updateQuestionnaireGeoNameAndCheckWhetherGeoNameIsChanged()
    {
        // create new geoname
        $geoName = new Geoname('foo geoname');
        $this->getEntityManager()->persist($geoName);
        $this->getEntityManager()->flush();
        $expected = $this->questionnaire->getGeoname()->getId();

        $data = array(
            'geoname' => $geoName->getId(),
        );

        $this->dispatch($this->getRoute('put') . '&fields=geoname', Request::METHOD_PUT, $data);
        $actual = $this->getJsonResponse();
        $this->assertNotEquals($expected, $actual['geoname']['id']);
    }

    /**
     * @test
     */
    public function createANewQuestionnaireWithPostMethod()
    {
        // Questionnaire
        $data = array(
            'dateObservationStart'    => '2013-05-22T00:00:00.000Z',
            'dateObservationEnd'    => '2014-05-22T00:00:00.000Z',
            'geoname' => $this->geoName->getId(),
            'survey'  => $this->survey->getId(),
            'status'  => 'new',
        );

        $this->dispatch($this->getRoute('post') . '?fields=survey', Request::METHOD_POST, $data);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['survey'], $actual['survey']['id']);
    }

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
            case 'getList':
                $route = sprintf(
                    '/api/survey/%s/questionnaire',
                    $this->survey->getId()
                );
                break;
            case 'get':
                $route = sprintf(
                    '/api/survey/%s/questionnaire/%s',
                    $this->survey->getId(),
                    $this->questionnaire->getId()
                );
                break;
            case 'post':
                $route = sprintf(
                    '/api/questionnaire'
                );
                break;
            case 'put':
                $route = sprintf(
                    '/api/questionnaire?id=%s',
                    $this->questionnaire->getId()
                );
                break;
            case 'delete':
                $route = sprintf(
                    '/api/questionnaire/%s',
                    $this->questionnaire->getId()
                );
                break;
            default:
                $route = '';

        }
        return $route;
    }
}
