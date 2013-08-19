<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

class ChartControllerTest extends AbstractController
{

    public function testGetValidChartStructure()
    {
        $this->dispatch('/api/chart', Request::METHOD_GET);

        $this->assertResponseStatusCode(200);

        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('chart', $data);
        $this->assertArrayHasKey('series', $data);
    }

    public function getValidDataProvider()
    {
        return new \ApiTest\JsonFileIterator('data/Api/Chart');
    }

    /**
     * @dataProvider getValidDataProvider
     */
    public function testGetValidData($params, $expectedJson, $message, $logFile)
    {
        $this->dispatch('/api/chart?' . $params, Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $actualJson = $this->getJsonResponse();
        $this->logJson($logFile, $actualJson);

        $this->assertEquals($expectedJson, $actualJson, $message);
    }

}
