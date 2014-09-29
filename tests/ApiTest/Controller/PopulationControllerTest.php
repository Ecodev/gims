<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

/**
 * @group Rest
 */
class PopulationControllerTest extends AbstractChildRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return array('id', 'year', 'population');
    }

    protected function getTestedObject()
    {
        return $this->population;
    }

    protected function getPossibleParents()
    {
        return [
            $this->questionnaire,
        ];
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

}
