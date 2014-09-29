<?php

namespace ApiTest\Controller\Rule;

use Zend\Http\Request;
use ApiTest\Controller\AbstractChildRestfulControllerTest;

/**
 * @group Rest
 */
abstract class AbstractUsageControllerTest extends AbstractChildRestfulControllerTest
{

    public function testCanUpdateUsage()
    {
        $data = array('justification' => 'foo');
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['justification'], $actual['justification']);

        // anonymous
        $this->identityProvider->setIdentity(null);
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(403);
    }

    public function testCannotUpdateUsageWithAnotherQuestionnaireUsage()
    {
        $questionnaire = $this->createAnotherQuestionnaire();

        // Update should be forbidden, because there is another questionnaire on
        // which we don't have acces and which is concerned by this usage
        $data = array('questionnaire' => $questionnaire->getId());
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(403);

        // Same for delete
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(403);

        // But we still should be able to read it
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $this->assertResponseStatusCode(200);
    }

}
