<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

abstract class AbstractChildRestfulControllerTest extends AbstractRestfulControllerTest
{

    public function testCanGetViaAllParents()
    {
        foreach ($this->getPossibleParents() as $parentPropertyName => $parent) {
            $reflectParent = new \ReflectionClass($parent);
            $parentName = lcfirst($reflectParent->getShortName());
            if (!is_string($parentPropertyName)) {
                $parentPropertyName = $parentName;
            }

            $url = str_replace('api/', 'api/' . $parentName . '/' . $parent->getId() . '/', $this->getRoute('getList'));
            $url .= '?fields=' . $parentPropertyName;
            $this->dispatch($url, Request::METHOD_GET);
            $this->assertResponseStatusCode(200);

            $actual = $this->getJsonResponse();
            $this->assertEquals(1, $actual['metadata']['totalCount'], 'should return only one record. With parent "' . $parentName . '"');
            $actualObject = $actual['items'][0];
            $this->assertEquals($this->getTestedObject()->getId(), $actualObject['id'], 'should be same object. With parent "' . $parentName . '"');

            if ($parentPropertyName != 'unidirectional') {
                if (isset($actualObject[$parentPropertyName]['id'])) {
                    $this->assertEquals($parent->getId(), $actualObject[$parentPropertyName]['id'], 'should be the parent. With parent "' . $parentName . '"');
                } else {
                    $this->assertEquals($parent->getId(), $actualObject[$parentPropertyName][0]['id'], 'the parent should be the only one in parent collection. With parent "' . $parentName . '"');
                }
            }
        }
    }

    abstract protected function getPossibleParents();
}
