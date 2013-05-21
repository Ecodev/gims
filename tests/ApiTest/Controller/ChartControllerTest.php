<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

class TableControllerTest extends AbstractController
{
    public function testGetValidChartStructure()
    {
        $this->dispatch('/api/chart', Request::METHOD_GET);

        $this->assertResponseStatusCode(200);

        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('chart', $data);
        $this->assertArrayHasKey('series', $data);
    }
}
