<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Role is as defined in RBAC system: http://en.wikipedia.org/wiki/Role-based_access_control
 *
 * @ORM\Entity(repositoryClass="Application\Repository\RoleRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="role_unique", columns={"name"})})
 */
class Role extends AbstractModel
{

    /**
     * @var array
     */
    protected static $jsonConfig
        = array(
            'name',
        );

    /**
     * @var array
     */
    protected static $relationProperties
        = array(
            'parent' => '\Application\Model\Role',
            'permission' => '\Application\Model\Permission',
        );

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var Role
     *
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $parent;

    /**
     * @ORM\ManyToMany(targetEntity="Permission")
     */
    private $permissions;

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setName($name);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Role
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set parent
     *
     * @param Role $parent
     * @return Role
     */
    public function setParent(Role $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Role
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add permission
     *
     * @param Permission $permission
     * @return Role
     */
    public function addPermission(Permission $permission)
    {
        $this->permissions->add($permission);

        return $this;
    }

    /**
     * Remove permission
     *
     * @param Permission $permission
     * @return Role
     */
    public function removePermission(Permission $permission)
    {
        $this->permissions->removeElement($permission);

        return $this;
    }

}
