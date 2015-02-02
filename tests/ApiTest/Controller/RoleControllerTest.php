<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

/**
 * @group Rest
 */
class RoleControllerTest extends AbstractRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return ['id', 'name'];
    }

    protected function getTestedObject()
    {
        return $this->surveyEditor;
    }

    protected function subtestAnonymousCannotDelete()
    {
        // Nothing to test, because not implemented
    }

    protected function subtestMemberCanDelete()
    {
        // Nothing to test, because not implemented
    }

    protected function subtestMemberCannotDeleteNonExisting()
    {
        // Nothing to test, because not implemented
    }

    public function testBuiltInRolesAreNotListed()
    {
        // Should fail without a questionnaire
        $this->dispatch($this->getRoute('getList'), Request::METHOD_GET);
        $this->assertResponseStatusCode(200);
        $actual = $this->getJsonResponse();

        foreach ($actual['items'] as $role) {
            $this->assertNotEquals('anonymous', $role['name'], 'anonymous should never appear in role list');
            $this->assertNotEquals('member', $role['name'], 'member should never appear in role list');
        }
    }
}
