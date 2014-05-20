<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserFilterSet links a user and a filterSet to give him a role
 * for that filterSet (hence permissions)
 * @ORM\Entity(repositoryClass="Application\Repository\UserFilterSetRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="user_filterset_unique",columns={"user_id", "filter_set_id", "role_id"})})
 */
class UserFilterSet extends AbstractUserRole
{

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="userFilterSets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $user;

    /**
     * @var FilterSet
     * @ORM\ManyToOne(targetEntity="FilterSet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $filterSet;

    /**
     * @var Role
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $role;

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'user',
            'role',
            'filterSet',
        ));
    }

    /**
     * Set "user"
     * @param User $user
     * @return self
     */
    public function setUser(User $user)
    {
        $this->user = $user;
        $user->userFilterSetAdded($this);

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
     * Set filterSet
     * @param FilterSet $filterSet
     * @return self
     */
    public function setFilterSet(FilterSet $filterSet)
    {
        $this->filterSet = $filterSet;

        return $this;
    }

    /**
     * Get filterSet
     * @return FilterSet
     */
    public function getFilterSet()
    {
        return $this->filterSet;
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

    /**
     * {@inheritdoc}
     */
    public function getRoleContextInternal($action)
    {
        return $this->getFilterSet();
    }

}
