<?php

namespace ApplicationTest\Model;

/**
 * @group Model
 */
class UserTest extends AbstractModel
{

    public function testActivationToken()
    {
        $user = new \Application\Model\User();
        $this->assertNull($user->getActivationToken(), 'new user have no token');

        $user->generateActivationToken();
        $token = $user->getActivationToken();
        $this->assertNotNull($token, 'once generated, it must not be NULL anymore');
        $this->assertEquals(32, strlen($token), 'must be exactly the length of DB field');

        $user->generateActivationToken();
        $token2 = $user->getActivationToken();
        $this->assertNotEquals($token2, $token, 'two tokens for same user must be different');

        $user->setLastLogin(new \DateTime());
        $this->assertNull($user->getActivationToken(), 'once user is logged in toekn must expire');

        $user->generateActivationToken();
        $this->assertNotNull($token, 'once generated, it must not be NULL anymore');
        $user->setState(1);
        $this->assertNull($user->getActivationToken(), 'once user is activated, cannot activate again');
    }

}
