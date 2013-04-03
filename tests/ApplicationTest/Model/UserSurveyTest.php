<?php

namespace ApplicationTest\Model;

class UserSurveyTest extends AbstractModel
{

    public function testUserRelation()
    {
        $userSurvey = new \Application\Model\UserSurvey();
        $user = new \Application\Model\User();
        $this->assertCount(0, $user->getUserSurveys(), 'collection is initialized on creation');

        $userSurvey->setUser($user);
        $this->assertCount(1, $user->getUserSurveys(), 'user must be notified when userSurvey is added');
        $this->assertSame($userSurvey, $user->getUserSurveys()->first(), 'original userSurvey can be retreived from user');
    }

}
