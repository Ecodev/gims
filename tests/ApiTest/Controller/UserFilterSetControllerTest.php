<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

/**
 * @group Rest
 */
class UserFilterSetControllerTest extends AbstractChildRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return ['id', 'user', 'role', 'filterSet'];
    }

    protected function getTestedObject()
    {
        return $this->userFilterSet;
    }

    protected function getPossibleParents()
    {
        return [
            $this->user,
            $this->filterSet,
        ];
    }

    public function testAnotherUserCannotGrantAccesToHimself()
    {
        $anotherUser = $this->createAnotherUser();

        $data = [
            'user' => $anotherUser->getId(),
            'role' => $this->filterEditor->getId(),
            'filterSet' => $this->filterSet->getId(),
        ];

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(403);
    }
}
