<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

/**
 * @group Rest
 */
class UserQuestionnaireControllerTest extends AbstractChildRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return array('id', 'user', 'role', 'questionnaire');
    }

    protected function getTestedObject()
    {
        return $this->userQuestionnaire1;
    }

    protected function getPossibleParents()
    {
        return [
            $this->user,
            $this->questionnaire,
        ];
    }

    public function testAnotherUserCannotGrantAccesToHimself()
    {
        $anotherUser = $this->createAnotherUser();

        $data = array(
            'user' => $anotherUser->getId(),
            'role' => $this->questionnaireReporter->getId(),
            'questionnaire' => $this->questionnaire->getId(),
        );

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(403);
    }

}
