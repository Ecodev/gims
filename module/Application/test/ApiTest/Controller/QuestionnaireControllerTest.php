<?php

namespace ApiTest\Controller;

class QuestionnaireControllerTest extends \ApplicationTest\Controller\AbstractController
{

    /**
     * @var \Application\Model\Survey
     */
    private $survey;

    /**
     * @var \Application\Model\Questionnaire
     */
    private $questionnaire;

    public function setUp()
    {
        parent::setUp();

        $this->survey = new \Application\Model\Survey();
        $this->survey->setActive(true);
        $this->survey->setName('test survey');
        $this->survey->setCode('code test survey');
        $this->survey->setYear(2010);

        $this->questionnaire = new \Application\Model\Questionnaire();
        $this->questionnaire->setSurvey($this->survey);
        $this->questionnaire->setDateObservationStart(new \DateTime('2010-01-01T00:00:00+0100'));
        $this->questionnaire->setDateObservationEnd(new \DateTime('2011-01-01T00:00:00+0100'));

        $this->getEntityManager()->persist($this->survey);
        $this->getEntityManager()->persist($this->questionnaire);
        $this->getEntityManager()->flush();
    }

    protected function getJsonResponse()
    {
        $content = $this->getResponse()->getContent();
        $json = \Zend\Json\Json::decode($content, \Zend\Json\Json::TYPE_ARRAY);

        $this->assertTrue(is_array($json));

        return $json;
    }

    protected function getExpectedJson()
    {
        return '{"id":' . $this->questionnaire->getId() . ',"dateObservationStart":"2010-01-01T00:00:00+0100","dateObservationEnd":"2011-01-01T00:00:00+0100","survey":{"id":' . $this->survey->getId() . ',"code":"code test survey","name":"test survey"}}';
    }

    public function testCanListQuestionnaire()
    {
        $this->dispatch('/api/questionnaire', \Zend\Http\Request::METHOD_GET);

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
        $this->dispatch('/api/questionnaire/' . $this->questionnaire->getId(), \Zend\Http\Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $this->getJsonResponse();
        $this->assertJsonStringEqualsJsonString($this->getExpectedJson(), $this->getResponse()->getContent());
    }

    public function testCanDeleteQuestionnaire()
    {
        // Should be able to delete once
        $this->dispatch('/api/questionnaire/' . $this->questionnaire->getId(), \Zend\Http\Request::METHOD_DELETE);
        $this->assertResponseStatusCode(200);
        $this->getJsonResponse();
        $this->assertJsonStringEqualsJsonString('{"message":"deleted successfully"}', $this->getResponse()->getContent());

        // Should not be able to delete the same resource again
        $this->dispatch('/api/questionnaire/' . $this->questionnaire->getId(), \Zend\Http\Request::METHOD_DELETE);
        $this->assertResponseStatusCode(404);
    }

}
