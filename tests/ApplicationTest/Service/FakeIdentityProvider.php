<?php

namespace ApplicationTest\Service;

use \Application\Model\User;

/**
 * @group Service
 */
class FakeIdentityProvider implements \ZfcRbac\Identity\IdentityProviderInterface
{

    /**
     * @var User
     */
    private $identity;

    /**
     * Set the identity
     * @param \Application\Model\User $identity
     * @return self
     */
    public function setIdentity(User $identity = null)
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * Gets the identity
     * @return User
     */
    public function getIdentity()
    {
        return $this->identity;
    }

}
