<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Permission is as defined in RBAC system: http://en.wikipedia.org/wiki/Role-based_access_control
 *
 * @ORM\Entity(repositoryClass="Application\Repository\PermissionRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="permission_unique", columns={"name"})})
 */
class Permission extends AbstractModel
{

    const CAN_MANAGE_ANSWER = 'can-manage-answer';

    const CAN_VALIDATE_QUESTIONNAIRE = 'can-validate-questionnaire';

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
