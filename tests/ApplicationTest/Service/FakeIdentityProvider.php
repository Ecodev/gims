<?php

namespace ApplicationTest\Service;

use \Application\Model\User;

class FakeIdentityProvider implements \ZfcRbac\Identity\IdentityProviderInterface
{

    /**
     * @var User
     */
    private $identity;

    /**
     *
     * @param \Application\Model\User $identity
     * @return \Application\Service\A
     */
    public function setIdentity(User $identity = null)
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     *
     * @return User
     */
    public function getIdentity()
    {
        return $this->identity;
    }

}
