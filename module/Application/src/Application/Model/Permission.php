<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;
use Application\Model\AbstractModel;

/**
 * Permission is as defined in RBAC system: http://en.wikipedia.org/wiki/Role-based_access_control
 *
 * @ORM\Entity(repositoryClass="Application\Repository\PermissionRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="permission_unique", columns={"name"})})
 */
class Permission extends AbstractModel
{

    /**
     * Returns the permission name for the given action, eg: "filter-delete"
     * @param \Application\Model\AbstractModel $object
     * @param string $action
     * @return string
     */
    public static function getPermissionName(AbstractModel $object, $action)
    {
        if ($object instanceof \Application\Model\Question\AbstractQuestion) {
            $name = 'Question'; // All questions use same permissions
        } else {
            $name = str_replace('Application\Model\\', '', get_class($object));
        }

        return $name . '-' . $action;
    }

    /**
     * @var array
     */
    protected static $jsonConfig = array();

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * Constructor
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->setName($name);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Permission
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

}
