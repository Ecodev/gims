<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

/**
 * @group Rest
 */
class PopulationControllerTest extends AbstractRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return array('id', 'year', 'population');
    }

    protected function getTestedObject()
    {
        return $this->population;
    }

    public function testCanUpdatePopulation()
    {
        $data = array('population' => '666666');
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['population'], $actual['population']);

        // anonymous
        $this->identityProvider->setIdentity(null);
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(403);
    }

    public function testCanCreatePopulation()
    {
        // Population
        $data = array(
            'country' => $this->country->getId(),
            'part' => $this->part->getId(),
            'year' => 2005,
            'population' => 666666,
        );

        // Should fail without a questionnaire
        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(403);

        // Should success with a questionnaire
        $data['questionnaire'] = $this->questionnaire->getId();
        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['population'], $actual['population']);

        // anonymous should fail
        $this->identityProvider->setIdentity(null);
        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(403);
    }

    public function testCanDeletePopulation()
    {
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(200);
        $this->assertEquals($this->getJsonResponse()['message'], 'Deleted successfully');
    }

    public function testAnonymousCannotDeletePopulation()
    {
        $this->identityProvider->setIdentity(null);
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(403);
    }

    public function testCannotDeleteNonExistingPopulation()
    {
        $this->dispatch('/api/population/713705', Request::METHOD_DELETE);
        $this->assertResponseStatusCode(404);
    }

}
