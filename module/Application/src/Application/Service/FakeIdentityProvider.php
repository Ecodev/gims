<?php

namespace Application\Service;

use \Application\Model\User;

/**
 * Used to impersonate user. This should only be used for test or CLI usage
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
