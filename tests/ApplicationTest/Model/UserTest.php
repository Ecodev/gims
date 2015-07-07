<?php

namespace ApplicationTest\Model;

/**
 * @group Model
 */
class UserTest extends AbstractModel
{
    public function testState()
    {
        $user = new \Application\Model\User();
        $this->assertEquals(0, $user->getState(), 'new user have not confirmed email');

        $user->setPassword('foo');
        $this->assertEquals(1, $user->getState(), 'if password was changed, email is considered confirmed');
    }

    public function testToken()
    {
        $user = new \Application\Model\User();
        $this->assertNull($user->getToken(), 'new user have no token');

        $user->generateToken();
        $token = $user->getToken();
        $this->assertNotNull($token, 'once generated, it must not be NULL anymore');
        $this->assertEquals(32, strlen($token), 'must be exactly the length of DB field');

        $user->generateToken();
        $token2 = $user->getToken();
        $this->assertNotEquals($token2, $token, 'two tokens for same user must be different');

        $user->setLastLogin(new \DateTime());
        $this->assertNull($user->getToken(), 'once user is logged in toekn must expire');

        $user->generateToken();
        $this->assertNotNull($token, 'once generated, it must not be NULL anymore');
        $user->setState(1);
        $this->assertNull($user->getToken(), 'once user is activated, cannot activate again');
    }
}
