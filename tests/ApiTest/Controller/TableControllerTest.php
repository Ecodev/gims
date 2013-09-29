<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

class TableControllerTest extends \ApplicationTest\Controller\AbstractController
{

    public function getValidDataProvider()
    {
        return new \ApiTest\JsonFileIterator('data/api/table/filter');
    }

    /**
     * @dataProvider getValidDataProvider
     */
    public function testGetValidData($params, $expectedJson, $message, $logFile)
    {
        $this->dispatch('/api/table/filter?' . $params, Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $this->assertNumericJson($expectedJson, $this->getResponse()->getContent(), $message, $logFile);
    }

}
