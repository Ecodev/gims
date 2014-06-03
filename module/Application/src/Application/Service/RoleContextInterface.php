<?php

namespace Application\Service;

/**
 * Interface to identify classes that can be used as context to fetch user's Roles
 */
interface RoleContextInterface
{

    public function getId();

    public function getName();
}
