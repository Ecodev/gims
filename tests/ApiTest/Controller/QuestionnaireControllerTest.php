<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

class QuestionnaireControllerTest extends AbstractController
{

    public function testEnsureOnlyAllowedFieldAreDisplayedInResponseForQuestionnaire()
    {
        $this->dispatch('/api/questionnaire/' . $this->questionnaire->getId(), Request::METHOD_GET);
        $allowedFields = array('id', 'dateObservationStart', 'dateObservationEnd', 'survey', 'name', 'geoname', 'completed', 'spatial', 'dateLastAnswerModification', 'reporterNames', 'validatorNames');
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
            $this->assertGreaterThan(0, $questionnaire['id']);
        }
    }

    public function testCanGetQuestionnaire()
    {
        $this->dispatch('/api/questionnaire/' . $this->questionnaire->getId(), Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $json = $this->getJsonResponse();
        $this->assertSame($this->questionnaire->getId(), $json['id']);
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
