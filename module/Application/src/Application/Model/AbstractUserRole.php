<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Base class for all User-role classes
 * @ORM\MappedSuperclass
 */
abstract class AbstractUserRole extends AbstractModel
{

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Role
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $role;

    /**
     * Set "user"
     * @param User $user
     * @return self
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get "user"
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set role
     * @param Role $role
     * @return self
     */
    public function setRole(Role $role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }
}
