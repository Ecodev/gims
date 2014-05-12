<?php

namespace Application\Service;

/**
 * Allow to NOT grant permission on an object which requires a context but does not have any.
 * This is especially useful on creation of object to be sure that the context is specified during creation.
 */
class MissingRequiredRoleContext implements \Application\Service\RoleContextInterface
{

    private $missingRoleContext;

    /**
     * @param string $missingRoleContext
     */
    public function __construct($missingRoleContext)
    {
        $this->missingRoleContext = $missingRoleContext;
    }

    public function getId()
    {
        return null;
    }

    public function getName()
    {
        return 'The following context is required but is missing: ' . $this->missingRoleContext;
    }

}
