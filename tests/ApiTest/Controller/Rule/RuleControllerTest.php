<?php

namespace ApiTest\Controller\Rule;

use Zend\Http\Request;
use ApiTest\Controller\AbstractRestfulControllerTest;

/**
 * @group Rest
 */
class RuleControllerTest extends AbstractRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return array('id', 'name', 'formula');
    }

    protected function getTestedObject()
    {
        return $this->rule;
    }

    public function testCanUpdateRule()
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

    public function testCanCreateRule()
    {
        // Rule
        $data = array(
            'name' => 'new-rule A',
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
