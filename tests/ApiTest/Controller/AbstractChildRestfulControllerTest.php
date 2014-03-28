<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

abstract class AbstractChildRestfulControllerTest extends AbstractRestfulControllerTest
{

    public function testCanGetViaAllParents()
    {
        foreach ($this->getPossibleParents() as $parent) {

            $reflect = new \ReflectionClass($parent);
            $parentName = strtolower($reflect->getShortName());
            $url = str_replace('api/', 'api/' . $parentName . '/' . $parent->getId() . '/', $this->getRoute('getList'));

            $this->dispatch($url, Request::METHOD_GET);
            $this->assertResponseStatusCode(200);

            $actual = $this->getJsonResponse();
            $this->assertEquals(1, $actual['metadata']['totalCount'], 'should return only one record with parent "' . $parentName . '"');
            $this->assertEquals($this->getTestedObject()->getId(), $actual['items'][0]['id'], 'should be same stuff with parent "' . $parentName . '"');
            $this->assertEquals($parent->getId(), $actual['items'][0][$parentName]['id'], 'should be the parent with parent "' . $parentName . '"');
        }
    }

    abstract protected function getPossibleParents();
}
