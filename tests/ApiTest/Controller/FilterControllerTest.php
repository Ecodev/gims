<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

/**
 * @group Rest
 */
class FilterControllerTest extends AbstractChildRestfulControllerTest
{

    use \ApiTest\Controller\Traits\ReferenceableInRule;

    protected function getAllowedFields()
    {
        return array('id', 'name');
    }

    protected function getTestedObject()
    {
        return $this->filter;
    }

    protected function getPossibleParents()
    {
        return [
            'filterSets' => $this->filterSet,
            'parents' => $this->filterParent,
        ];
    }

    public function testCanUpdateFilter()
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

    public function testCanCreateFilter()
    {
        // Filter
        $data = array(
            'name' => 'new-filter A',
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

}
