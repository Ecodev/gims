<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

class TableControllerTest extends AbstractController
{
    public function testGetValidTableStructure()
    {
        $this->dispatch('/api/table?questionnaire=' . $this->questionnaire->getId(), Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $this->getJsonResponse();
    }
}
