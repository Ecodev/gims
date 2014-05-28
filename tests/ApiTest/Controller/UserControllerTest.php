<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

/**
 * @group Rest
 */
class UserControllerTest extends AbstractRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return array('id', 'name', 'email', 'state', 'lastLogin');
    }

    protected function getTestedObject()
    {
        return $this->user;
    }

    protected function subtestAnonymousCannotDelete()
    {
        // Actually cannot delete user
        $this->identityProvider->setIdentity($this->user);
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(500);
    }

    protected function subtestMemberCanDelete()
    {
        // Actually cannot delete user
        $this->identityProvider->setIdentity($this->user);
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(500);
    }

    public function subtestMemberCannotDeleteNonExisting()
    {
        // Actually cannot delete part
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(500);
    }

    public function testCanGetNeverGetPassword()
    {
        $this->dispatch($this->getRoute('get') . '?fields=password,phone', Request::METHOD_GET);
        $this->assertResponseStatusCode(200);

        $actual = $this->getJsonResponse();
        $keys = array_keys($actual);
        $this->assertTrue(in_array('phone', $keys), "API should return field: 'phone'");
        $this->assertFalse(in_array('password', $keys), "API should never return password");
    }

    public function testSearchProvider()
    {
        return array(
            array('?q=test user unit tests', 1),
            array('?q=impossible thing to find', 0), // repetition doesn't matter
            array('?q=" AND SQL injection', 0), // cannot inject SQL
            array('?q=\' AND SQL injection', 0), // cannot inject SQL
        );
    }

    /**
     * @dataProvider testSearchProvider
     */
    public function testSearch($params, $count)
    {
        $this->dispatch($this->getRoute('getList') . $params, Request::METHOD_GET);
        $actual = $this->getJsonResponse();

        $this->assertEquals($count, $actual['metadata']['totalCount'], 'result count does not match expectation');
    }

}
