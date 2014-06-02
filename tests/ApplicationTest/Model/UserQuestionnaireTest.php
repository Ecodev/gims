<?php

namespace ApplicationTest\Model;

/**
 * @group Model
 */
class UserQuestionnaireTest extends AbstractModel
{

    public function testUserRelation()
    {
        $userQuestionnaire = new \Application\Model\UserQuestionnaire();
        $user = new \Application\Model\User();
        $this->assertCount(0, $user->getUserQuestionnaires(), 'collection is initialized on creation');

        $userQuestionnaire->setUser($user);
        $this->assertCount(1, $user->getUserQuestionnaires(), 'user must be notified when userQuestionnaire is added');
        $this->assertSame($userQuestionnaire, $user->getUserQuestionnaires()->first(), 'original userQuestionnaire can be retrieved from user');
    }

}
