<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

class QuestionnaireControllerTest extends AbstractController
{

    protected function getExpectedJson()
    {
        return '{"id":' . $this->questionnaire->getId() . ',"name":"code test survey - test geoname","dateObservationStart":"2010-01-01T00:00:00+0100","dateObservationEnd":"2011-01-01T00:00:00+0100","survey":{"id":' . $this->survey->getId() . ',"code":"code test survey","name":"test survey"},"geoname":{"id":' . $this->geoName->getId() . ',"name":"test geoname"}}';
    }

    public function testEnsureOnlyAllowedFieldAreDisplayedInResponseForQuestionnaire()
    {
        $this->dispatch('/api/questionnaire/' . $this->questionnaire->getId(), Request::METHOD_GET);
        $allowedFields = array('id', 'dateObservationStart', 'dateObservationEnd', 'survey', 'name', 'geoname');
        foreach ($this->getJsonResponse() as $key => $value) {
            $this->assertTrue(in_array($key, $allowedFields));
        }
    }

    public function testCanListQuestionnaire()
    {
        $this->dispatch('/api/questionnaire', Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $json = $this->getJsonResponse();

        // In the array of all questionnaires, we should at least found the test questionnaire
        foreach ($json as $questionnaire) {
            if ($questionnaire['id'] == $this->questionnaire->getId()) {
                $singleJson = \Zend\Json\Json::encode($questionnaire);
                $this->assertJsonStringEqualsJsonString($this->getExpectedJson(), $singleJson);
            }
        }
    }

    public function testCanGetQuestionnaire()
    {
        $this->dispatch('/api/questionnaire/' . $this->questionnaire->getId(), Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $this->getJsonResponse();
        $this->assertJsonStringEqualsJsonString($this->getExpectedJson(), $this->getResponse()->getContent());
    }

    public function testCanDeleteQuestionnaire()
    {
        // Should be able to delete once
        $this->dispatch('/api/questionnaire/' . $this->questionnaire->getId(), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(200);
        $this->getJsonResponse();
        $this->assertJsonStringEqualsJsonString('{"message":"deleted successfully"}', $this->getResponse()->getContent());

        // Should not be able to delete the same resource again
        $this->dispatch('/api/questionnaire/' . $this->questionnaire->getId(), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(404);
    }

}
