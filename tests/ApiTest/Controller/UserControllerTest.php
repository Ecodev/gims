<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

class UserControllerTest extends AbstractController
{

    /**
     * Get suitable route for GET method.
     *
     * @param string $method
     *
     * @return string
     */
    private function getRoute($method)
    {
        switch ($method) {
            case 'getList':
                $route = '/api/user';
                break;
            case 'get':
                $route = sprintf(
                        '/api/user/%s', $this->user->getId()
                );
                break;
            case 'post':
                $route = '/api/user';
                break;
            case 'put':
                $route = sprintf(
                        '/api/user/%s', $this->user->getId()
                );
                break;
            default:
                $route = '';
        }

        return $route;
    }

    /**
     * @group UserApi
     */
    public function testCanGetOneUser()
    {
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $this->assertResponseStatusCode(200);

        $actual = $this->getJsonResponse();
        $allowedFields = array(
            'id',
            'name',
            'email',
            'state',
            'lastLogin'
        );
        foreach ($actual as $key => $value) {
            $this->assertTrue(in_array($key, $allowedFields), "API should not return non-allowed field: '" . $key . "'");
        }

        $this->assertSame($this->user->getId(), $actual['id'], 'should be the same ID that what we asked');
    }

    /**
     * @group UserApi
     */
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
     * @group UserApi
     * @dataProvider testSearchProvider
     */
    public function testSearch($params, $count)
    {
        $this->dispatch($this->getRoute('getList') . $params, Request::METHOD_GET);
        $actual = $this->getJsonResponse();

        $this->assertEquals($count, $actual['metadata']['totalCount'], 'result count does not match expectation');
    }

}
