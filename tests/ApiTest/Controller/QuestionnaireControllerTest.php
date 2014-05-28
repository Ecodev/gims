<?php

namespace ApiTest\Controller;

use Application\Model\Geoname;
use Zend\Http\Request;

/**
 * @group Rest
 */
class QuestionnaireControllerTest extends AbstractChildRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return array('id', 'dateObservationStart', 'dateObservationEnd', 'survey', 'name', 'geoname', 'completed', 'spatial', 'dateLastAnswerModification', 'reporterNames', 'validatorNames', 'comments', 'status', 'permission');
    }

    protected function getTestedObject()
    {
        return $this->questionnaire;
    }

    protected function getPossibleParents()
    {
        return [
            $this->survey,
        ];
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
        if ($method == 'getListSurvey') {
            return sprintf('/api/survey/%s/questionnaire', $this->survey->getId());
        } else {
            return parent::getRoute($method);
        }
    }

    public function testCanListQuestionnaire()
    {
        $this->dispatch($this->getRoute('getListSurvey'), Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $json = $this->getJsonResponse();

        // In the array of all questionnaires, we should at least found the test questionnaire
        foreach ($json['items'] as $questionnaire) {
            $this->assertGreaterThan(0, $questionnaire['id']);
        }
    }

    public function testCanUpdateQuestionnaireGeoname()
    {
        // create new geoname
        $geoname = new Geoname('foo geoname');
        $this->getEntityManager()->persist($geoname);
        $this->getEntityManager()->flush();
        $expected = $this->questionnaire->getGeoname()->getId();

        $data = array(
            'geoname' => $geoname->getId(),
        );

        $this->dispatch($this->getRoute('put') . '?fields=geoname', Request::METHOD_PUT, $data);
        $actual = $this->getJsonResponse();
        $this->assertNotEquals($expected, $actual['geoname']['id']);
    }

    public function testCanCreateQuestionnaire()
    {
        // Questionnaire
        $data = array(
            'dateObservationStart' => '2013-05-22T00:00:00.000Z',
            'dateObservationEnd' => '2014-05-22T00:00:00.000Z',
            'geoname' => $this->geoname->getId(),
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
     * @param string $params
     * @param integer $count
     */
    public function testSearch($params, $count)
    {
        $this->dispatch($this->getRoute('getListSurvey') . $params, Request::METHOD_GET);
        $actual = $this->getJsonResponse();

        $this->assertEquals($count, $actual['metadata']['totalCount'], 'result count does not match expectation');
    }

}
