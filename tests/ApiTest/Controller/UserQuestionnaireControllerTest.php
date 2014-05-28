<?php

namespace ApiTest\Controller;

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

    public function testCanGetViaAllParents()
    {
        $this->getEntityManager()->remove($this->getEntityManager()->merge($this->userQuestionnaire2));
        $this->getEntityManager()->flush();

        parent::testCanGetViaAllParents();
    }

}
