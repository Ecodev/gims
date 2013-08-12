<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

class TableControllerTest extends AbstractController
{
    public function testGetValidTableStructureWithMissingParameter()
    {
        $this->dispatch('/api/table?questionnaire=' . $this->questionnaire->getId(), Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $this->getJsonResponse();
    }

    public function testGetValidData()
    {
        $this->dispatch('/api/table?questionnaire=' . $this->questionnaire->getId() . '&filterSet=' . $this->filterSet->getId(), Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $this->getJsonResponse();
    }
}
