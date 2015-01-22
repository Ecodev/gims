<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

/**
 * @group Rest
 */
class UserSurveyControllerTest extends AbstractChildRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return ['id', 'user', 'role', 'survey'];
    }

    protected function getTestedObject()
    {
        return $this->userSurvey;
    }

    protected function getPossibleParents()
    {
        return [
            $this->user,
            $this->survey,
        ];
    }

    public function testAnotherUserCannotGrantAccesToHimself()
    {
        $anotherUser = $this->createAnotherUser();

        $data = [
            'user' => $anotherUser->getId(),
            'role' => $this->surveyEditor->getId(),
            'survey' => $this->survey->getId(),
        ];

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(403);
    }

}
