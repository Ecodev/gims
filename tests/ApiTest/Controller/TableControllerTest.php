<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

class TableControllerTest extends \ApplicationTest\Controller\AbstractController
{

    public function getValidDataProvider()
    {
        return new \ApiTest\JsonFileIterator('data/Api/Table');
    }

    /**
     * @dataProvider getValidDataProvider
     */
    public function testGetValidData($params, $expectedJson, $message, $logFile)
    {
        $this->dispatch('/api/table?' . $params, Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $actualJson = $this->getJsonResponse();
        $this->logJson($logFile, $actualJson);

        $this->assertEquals($expectedJson, $actualJson, $message);
    }

}
