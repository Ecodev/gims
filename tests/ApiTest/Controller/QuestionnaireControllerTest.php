<?php

namespace ApiTest\Controller;

use Application\Model\Geoname;
use Zend\Http\Request;

/**
 * @group Rest
 */
class QuestionnaireControllerTest extends AbstractRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return array('id', 'dateObservationStart', 'dateObservationEnd', 'survey', 'name', 'geoname', 'completed', 'spatial', 'dateLastAnswerModification', 'reporterNames', 'validatorNames', 'comments', 'status', 'permission');
    }

    protected function getTestedObject()
    {
        return $this->questionnaire;
    }

    /**
     * Get suitable route for GET method.
     *
     * @param string $method
     *
     * @return string
     */
    protected function getRoute($method)
    {
        switch ($method) {
            case 'getList':
                $route = sprintf('/api/survey/%s/questionnaire', $this->survey->getId());
                break;
            case 'post':
                $route = sprintf('/api/questionnaire');
                break;
            case 'get':
            case 'put':
            case 'delete':
                $route = sprintf('/api/questionnaire/%s', $this->questionnaire->getId());
                break;
            default:
                throw new \Exception("Unsupported route '$method' for questionnaire");
        }

        return $route;
    }

    public function testCanListQuestionnaire()
    {
        $this->dispatch($this->getRoute('getList'), Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $json = $this->getJsonResponse();

        // In the array of all questionnaires, we should at least found the test questionnaire
        foreach ($json['items'] as $questionnaire) {
            $this->assertGreaterThan(0, $questionnaire['id']);
        }
    }

    public function testQuestionnaireWithNoAnswerCanBeDeleted()
    {
        // Questionnaire
        $data = array(
            'dateObservationStart' => '2013-05-22T00:00:00.000Z',
            'dateObservationEnd' => '2014-05-22T00:00:00.000Z',
            'geoname' => $this->geoName->getId(),
            'survey' => $this->survey->getId(),
        );

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $actual = $this->getJsonResponse();

        // Should be able to delete once
        $this->dispatch('/api/questionnaire/' . $actual['id'], Request::METHOD_DELETE);
        $this->assertResponseStatusCode(200);
        $this->getJsonResponse();
        $this->assertJsonStringEqualsJsonString(
                '{"message":"Deleted successfully"}', $this->getResponse()->getContent()
        );

        // Should not be able to delete the same resource again
        $this->dispatch('/api/questionnaire/' . $actual['id'], Request::METHOD_DELETE);
        $this->assertResponseStatusCode(404);
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

        $this->dispatch($this->getRoute('put') . '?fields=geoname', Request::METHOD_PUT, $data);
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
            'dateObservationStart' => '2013-05-22T00:00:00.000Z',
            'dateObservationEnd' => '2014-05-22T00:00:00.000Z',
            'geoname' => $this->geoName->getId(),
            'survey' => $this->survey->getId(),
            'status' => 'new',
        );

        $this->dispatch($this->getRoute('post') . '?fields=survey', Request::METHOD_POST, $data);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['survey'], $actual['survey']['id']);
    }

    public function testSearchProvider()
    {
        return array(
            array('?q=test geoname', 1), // can search by geoname
            array('?q=code test survey', 1), // can search by survey code
            array('?q=code test survey test geoname', 1), // can search by both
        );
    }

    /**
     * @dataProvider testSearchProvider
     */
    public function testSearch($params, $count)
    {
        $this->dispatch($this->getRoute('getList') . $params, Request::METHOD_GET);
        $actual = $this->getJsonResponse();

        $this->assertEquals($count, $actual['metadata']['totalCount'], 'result count does not match expectation');
    }

}
