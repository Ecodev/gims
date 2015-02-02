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
        return ['id', 'name'];
    }

    protected function getTestedObject()
    {
        return $this->filterSet;
    }

    public function testCanUpdateFilterSet()
    {
        $data = ['name' => 'foo'];
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
        $data = [
            'name' => 'new-filterSet',
        ];

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['name'], $actual['name']);

        // anonymous
        $this->identityProvider->setIdentity(null);
        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(403);
    }

    public function testAnonymousCanGetPublishedFilterSet()
    {
        // Anonymous should not be able to get a filterSet on which he has no access
        $this->identityProvider->setIdentity(null);
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $this->assertResponseStatusCode(403);
        $this->dispatch($this->getRoute('getList'), Request::METHOD_GET);
        $actual = $this->getJsonResponse();

        $this->assertEquals(6, $actual['metadata']['totalCount'], 'should be not able to be listed');

        // Publish the filterSet
        $this->filterSet = $this->getEntityManager()->merge($this->filterSet);
        $this->filterSet->setIsPublished(true);
        $this->getEntityManager()->flush();

        // Should be able to get it now
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);

        $this->assertResponseStatusCode(200);
        $this->dispatch($this->getRoute('getList'), Request::METHOD_GET);
        $actual = $this->getJsonResponse();
        $this->assertEquals(7, $actual['metadata']['totalCount'], 'should be able to be listed');
    }
}
