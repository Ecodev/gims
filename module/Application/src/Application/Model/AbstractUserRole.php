<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Base class for all User-role classes
 */
abstract class AbstractUserRole extends AbstractModel
{

    /**
     * Ensures that a permission can't be added to an object if it's not
     * persisted.
     * E.g. when giving "Filter Editor" role to a FilterSet, the AbstractModel
     * hydrates the UserFilterSet object to recover context and then verify
     * if rights are granted. The objects used is in memory and we have
     * officially not the permissions.
     * This method ensures that the objects used to test permissions is in database.
     * @param string $action
     * @return \Application\Service\RoleContextInterface|null
     */
    public function getRoleContext($action)
    {
        if ($this->getId()) {
            return $this->getRoleContextInternal();
        } else {
            return null;
        }
    }

}
