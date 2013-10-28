<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

class TableControllerTest extends \ApplicationTest\Controller\AbstractController
{

    public function getValidDataFilterProvider()
    {
        return new \ApiTest\JsonFileIterator('data/api/table/filter');
    }

    /**
     * @dataProvider getValidDataFilterProvider
     */
    public function testGetValidDataFilter($params, $expectedJson, $message, $logFile)
    {
        $this->dispatch('/api/table/filter?' . $params, Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $this->assertNumericJson($expectedJson, $this->getResponse()->getContent(), $message, $logFile);
    }

    public function getValidDataQuestionnaireProvider()
    {
        return new \ApiTest\JsonFileIterator('data/api/table/questionnaire');
    }

    /**
     * @dataProvider getValidDataQuestionnaireProvider
     */
    public function testGetValidDataQuestionnaire($params, $expectedJson, $message, $logFile)
    {
        $this->dispatch('/api/table/questionnaire?' . $params, Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $this->assertNumericJson($expectedJson, $this->getResponse()->getContent(), $message, $logFile);
    }

}
