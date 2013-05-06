<?php

namespace Api\Service;


class Permission
{

    /**
     * Returns whether a User can access a field
     */
    public function isFieldAllowed($fieldName)
    {

        // @todo create a mechanism
        // class Permission must implement ServiceLocatorAwareInterface
        #$rbac = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac');

        return true;
    }
}
