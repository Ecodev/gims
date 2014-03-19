<?php

namespace ApplicationTest\Model;

class UserFilterSetTest extends AbstractModel
{

    public function testUserRelation()
    {
        $userFilterSet = new \Application\Model\UserFilterSet();
        $user = new \Application\Model\User();
        $this->assertCount(0, $user->getUserFilterSets(), 'collection is initialized on creation');

        $userFilterSet->setUser($user);
        $this->assertCount(1, $user->getUserFilterSets(), 'user must be notified when userQuestionnaire is added');
        $this->assertSame($userFilterSet, $user->getUserFilterSets()->first(), 'original userQuestionnaire can be retreived from user');
    }

}
