<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

/**
 * @group Rest
 */
class PartControllerTest extends AbstractRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return ['id', 'name'];
    }

    protected function getTestedObject()
    {
        return $this->part;
    }

    protected function subtestMemberCanDelete()
    {
        // Actually cannot delete part
        $this->identityProvider->setIdentity($this->user);
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(403);
    }

    public function subtestMemberCannotDeleteNonExisting()
    {
        // Actually cannot delete part
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(403);
    }

    public function testCannotCreatePart()
    {
        $data = [
            'name' => 'this will fail',
        ];

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(403);

        // Anonymous will also fail
        $this->identityProvider->setIdentity(null);
        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(403);
    }

    public function testPaginationProvider()
    {
        return [
            ['', 1, 25, 2], // default pagination
            ['?page=1&perPage=2', 1, 2, 2],
            ['?page=1&perPage=1', 1, 1, 1],
            ['?page=2&perPage=1', 2, 1, 1],
            ['?page=1&perPage=0', 1, 0, 0],
            ['?page=abc&perPage=abc', 1, 0, 0], // invalid params does not crash
            ['?page=999&perPage=9999', 999, 1000, 0], // excessive perPage is capped
        ];
    }

    /**
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
        $this->assertGreaterThanOrEqual(min([2, $count]), count($actual['items']), 'should have at least the two items injected during tests or the perPage specified');
    }

    public function testSearchProvider()
    {
        return [
            ['?q=test part', 3],
            ['?q=TeSt PaRt', 3], // case insensitive
            ['?q=part test', 3], // order doesn't matter
            ['?q= part   test  ', 3], // extra spaces
            ['?q=3 test part', 1], // more words restrict more the result
            ['?q=3 test part 3 test part', 1], // repetition doesn't matter
            ['?q=impossible thing to find', 0], // repetition doesn't matter
            ['?q=" AND SQL injection', 0], // cannot inject SQL
            ['?q=\' AND SQL injection', 0], // cannot inject SQL
        ];
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
