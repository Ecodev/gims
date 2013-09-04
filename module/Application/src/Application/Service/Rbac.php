<?php

namespace Application\Service;

use Application\Model\AbstractModel;

class Rbac extends \ZfcRbac\Service\Rbac
{
    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

    /**
     * @var string
     */
    private $message;

    /**
     * Returns whether the currently logged user is allowed to do the action on the given object
     * @param AbstractModel $object
     * @param string $action a standard crud actions (create, read, update, delete), or any other specialized action (eg: 'validate' for questionnaire)
     * @return boolean
     */
    public function isActionGranted(AbstractModel $object, $action)
    {
        /* @var $rbac \Application\Service\Rbac */
        $rbac = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac');

        // The original creator of object always has all access
        $creator = $object->getCreator();
        if ($creator && $creator == $rbac->getIdentity()) {
            return true;
        }

        $permission = \Application\Model\Permission::getPermissionName($object, $action);
        $context = $object->getRoleContext();

        if ($context) {
            $result = $rbac->isGrantedWithContext($context, $permission);
        } else {
            $result = $rbac->isGranted($permission);
        }

        $this->setMessage($result, $object, $permission);

        return $result;
    }

    /**
     * Format a message in case of access denied
     * @param boolean $isGranted
     * @param \Application\Model\AbstractModel $object
     * @param string $permission
     * @return void
     */
    protected function setMessage($isGranted, AbstractModel $object, $permission)
    {
        if ($isGranted) {
            $this->message = null;
        } else {

            /* @var $rbac \Application\Service\Rbac */
            $rbac = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac');
            $roles = implode(', ', $rbac->getIdentity()->getRoles());
            $this->message = 'Insufficient access rights for permission "' . $permission . '" on "' . get_class($object) . '#' . $object->getId() . '" with your current roles: ' . $roles;
        }
    }

    /**
     * Returns the last error message denied action
     * @return string|null null if was granted, message otherwise
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns true if the user has the permission in the given context.
     *
     * @param \Application\Service\RoleContextInterface $context
     * @param string                          $permission
     * @param null|Closure|AssertionInterface $assert
     * @throws InvalidArgumentException
     * @return bool
     */
    public function isGrantedWithContext(RoleContextInterface $context, $permission, $assert = null)
    {
        // Get the user to set the context for role
        $user = $this->getIdentity();
        if ($user instanceof \Application\Model\User) {
            $user->setRolesContext($context);
        }

        $result = $this->isGranted($permission, $assert);

        // Reset context to avoid side-effect on next usage of $this->isGranted()
        if ($user instanceof \Application\Model\User) {
            $user->resetRolesContext();
        }

        return $result;
    }

}
