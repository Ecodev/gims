<?php

namespace ApiTest\Controller\Rule;

use Zend\Http\Request;

/**
 * @group Rest
 */
class QuestionnaireUsageControllerTest extends AbstractUsageControllerTest
{

    protected function getAllowedFields()
    {
        return array('id', 'rule', 'questionnaire', 'part', 'justification');
    }

    protected function getTestedObject()
    {
        return $this->questionnaireUsage;
    }

    protected function getPossibleParents()
    {
        return array(
            $this->questionnaireUsage->getRule(),
            $this->questionnaireUsage->getQuestionnaire(),
        );
    }

    public function getComputedQuestionnaireUsageProvider()
    {
        return new \ApiTest\JsonFileIterator('data/api/questionnaireUsage/compute');
    }

    /**
     * @dataProvider getComputedQuestionnaireUsageProvider
     * @group LongTest
     */
    public function testgetComputedQuestionnaireUsage($params, $expectedJson, $message, $logFile)
    {
        $this->dispatch('/api/questionnaireUsage/compute?' . $params, Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $this->assertNumericJson($expectedJson, $this->getResponse()->getContent(), $message, $logFile);
    }

    public function testCannotUpdateRuleWithPublishedQuestionnaire()
    {
        $data = array('justification' => 'foo');
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['justification'], $actual['justification']);

        // Change questionnaire to be published
        $this->questionnaire->setStatus(\Application\Model\QuestionnaireStatus::$PUBLISHED);
        $this->getEntityManager()->merge($this->questionnaire);
        $this->getEntityManager()->flush();

        // Now, the same operation should be forbidden, because the questionnaire is published
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(403);
    }

}
