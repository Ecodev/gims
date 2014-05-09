<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

/**
 * @group Rest
 */
class FilterSetControllerTest extends AbstractRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return array('id', 'name');
    }

    protected function getTestedObject()
    {
        return $this->filterSet;
    }

    public function testCanUpdateFilterSet()
    {
        $data = array('name' => 'foo');
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['name'], $actual['name']);

        // anonymous
        $this->identityProvider->setIdentity(null);
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(403);
    }

    public function testCanCreateFilterSet()
    {
        // FilterSet
        $data = array(
            'name' => 'new-filterSet',
            'excludedFilters' => array($this->filter->getId()),
            'originalFilterSet' => $this->filterSet->getId(),
        );

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['name'], $actual['name']);

        // anonymous
        $this->identityProvider->setIdentity(null);
        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(403);
    }

    public function testCanDeleteFilterSet()
    {
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(200);
        $this->assertEquals($this->getJsonResponse()['message'], 'Deleted successfully');
    }

    public function testAnonymousCanDeleteFilterSet()
    {
        $this->identityProvider->setIdentity(null);
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(403);
    }

    public function testCannotDeleteNonExistingFilterSet()
    {
        $this->dispatch('/api/filterSet/713705', Request::METHOD_DELETE); // smyle, the sun shines :)
        $this->assertResponseStatusCode(404);
    }

}
