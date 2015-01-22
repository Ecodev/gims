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
    protected $user;

    /**
     * @var FilterSet
     * @ORM\ManyToOne(targetEntity="FilterSet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $filterSet;

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), [
            'user',
            'role',
            'filterSet',
        ]);
    }

    /**
     * Set "user"
     * @param User $user
     * @return self
     */
    public function setUser(User $user)
    {
        parent::setUser($user);
        $user->userFilterSetAdded($this);

        return $this;
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
     * {@inheritdoc}
     */
    public function getRoleContext($action)
    {
        return $this->getFilterSet();
    }

}
