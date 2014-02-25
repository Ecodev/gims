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
        $expected = $this->filterSet->getName();
        $data = array(
            'name' => $this->filterSet->getName() . 'foo',
        );

        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['name'], $actual['name']);
    }

    public function testCanUpdateFilterSetAsAnonymous()
    {
        $this->rbac->setIdentity(null);
        $this->testCanUpdateFilterSet();
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
    }

    public function testAnonymousCanCreateFilterSet()
    {
        $this->rbac->setIdentity(null);
        $this->testCanCreateFilterSet();
    }

    public function testCanDeleteFilterSet()
    {
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(200);
        $this->assertEquals($this->getJsonResponse()['message'], 'Deleted successfully');
    }

    public function testCannotDeleteNonExistingFilterSet()
    {
        $this->dispatch('/api/filterSet/' . ($this->filterSet->getId() + 1), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(404);
    }

}
