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

}
