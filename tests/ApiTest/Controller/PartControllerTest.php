<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

class PartControllerTest extends AbstractController
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
                $route = '/api/part';
                break;
            case 'get':
                $route = sprintf(
                        '/api/part/%s', $this->part->getId()
                );
                break;
            case 'post':
                $route = '/api/part';
                break;
            case 'put':
                $route = sprintf(
                        '/api/part/%s', $this->part->getId()
                );
                break;
            default:
                $route = '';
        }

        return $route;
    }

    /**
     * @group PartApi
     */
    public function testCanGetOnePart()
    {
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $this->assertResponseStatusCode(200);

        $actual = $this->getJsonResponse();
        $allowedFields = array('id', 'name');
        foreach ($actual as $key => $value) {
            $this->assertTrue(in_array($key, $allowedFields), "API should not return non-allowed field: '" . $key . "'");
        }

        $this->assertSame($this->part->getId(), $actual['id'], 'should be the same ID that what we asked');
    }

    /**
     * @group PartApi
     */
    public function testCannotCreatePart()
    {
        $data = array(
            'name' => 'this will fail',
        );

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(403);
    }

    /**
     * @group PartApi
     */
    public function testCannotCreatePartWithRoleAnonymous()
    {
        $data = array(
            'name' => 'this will fail',
        );

        $this->rbac->setIdentity(null);
        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(403);
    }

    public function testPaginationProvider()
    {
        return array(
            array('', 1, 25, 2), // default pagination
            array('?page=1&perPage=2', 1, 2, 2),
            array('?page=1&perPage=1', 1, 1, 1),
            array('?page=2&perPage=1', 2, 1, 1),
            array('?page=1&perPage=0', 1, 0, 0),
            array('?page=abc&perPage=abc', 1, 0, 0), // invalid params does not crash
            array('?page=999&perPage=9999', 999, 1000, 0), // excessive perPage is capped
        );
    }

    /**
     * @group PartApi
     * @dataProvider testPaginationProvider
     */
    public function testPagination($params, $page, $perPage, $count)
    {
        $this->dispatch($this->getRoute('getList') . $params, Request::METHOD_GET);
        $actual = $this->getJsonResponse();

        $this->assertTrue(is_array($actual['metadata']), 'metadata should always exists');
        $this->assertTrue(is_array($actual['items']), 'items should always exists');
        $this->assertEquals($page, $actual['metadata']['page'], 'current page number should be what we asked for');
        $this->assertEquals($perPage, $actual['metadata']['perPage'], 'current item per page should be what we asked for');
        $this->assertGreaterThanOrEqual(2, $actual['metadata']['totalCount'], 'should have a totalCount of at least 2 (the parts injected during tests)');
        $this->assertGreaterThanOrEqual(min(array(2, $count)), count($actual['items']), 'should have at least the two items injected during tests or the perPage specified');
    }

    public function testSearchProvider()
    {
        return array(
            array('?q=test part', 3),
            array('?q=TeSt PaRt', 3), // case insensitive
            array('?q=part test', 3), // order doesn't matter
            array('?q= part   test  ', 3), // extra spaces
            array('?q=3 test part', 1), // more words restrict more the result
            array('?q=3 test part 3 test part', 1), // repetition doesn't matter
            array('?q=impossible thing to find', 0), // repetition doesn't matter
            array('?q=" AND SQL injection', 0), // cannot inject SQL
            array('?q=\' AND SQL injection', 0), // cannot inject SQL
        );
    }

    /**
     * @group PartApi
     * @dataProvider testSearchProvider
     */
    public function testSearch($params, $count)
    {
        $this->dispatch($this->getRoute('getList') . $params, Request::METHOD_GET);
        $actual = $this->getJsonResponse();

        $this->assertEquals($count, $actual['metadata']['totalCount'], 'result count does not match expectation');
    }

}
