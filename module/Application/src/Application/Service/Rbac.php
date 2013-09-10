<?php

namespace Application\Service;

use Application\Model\AbstractModel;
use Application\Assertion\AbstractAssertion;

/**
 * This class allow us to query permission model and find out if the current
 * user is allowed to do things.
 *
 * The main method is <code>isActionGranted()</code> and should be used for most
 * cases. It automates almost everything for the most common cases.
 *
 * Then, lower-level API, <code>isGrantedWithContext()</code> and
 * <code>isGranted()</code> can be used for more specific cases.
 */
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

        $permission = \Application\Model\Permission::getPermissionName($object, $action);
        $context = $object->getRoleContext($action);
        $assertion = $this->getAssertion($object, $action);

        if ($context) {
            $result = $rbac->isGrantedWithContext($context, $permission, $assertion);
        } else {
            $result = $rbac->isGranted($permission, $assertion);
        }

        $this->setMessage($result, $object, $permission, $context, $assertion);

        return $result;
    }

    /**
     * Format a message in case of access denied
     * @param boolean $isGranted
     * @param AbstractModel $object
     * @param string $permission
     * @param RoleContextInterface $context
     * @param AbstractAssertion $assertion
     * @return void
     */
    private function setMessage($isGranted, AbstractModel $object, $permission, RoleContextInterface $context = null, AbstractAssertion $assertion = null)
    {
        if ($isGranted) {
            $this->message = null;
        } elseif ($assertion && $assertion->getMessage()) {
            $this->message = $assertion->getMessage();
        } else {

            $user = $this->getIdentity();
            if ($user instanceof \Application\Model\User && $context) {
                $user->setRolesContext($context);
            }

            $roles = implode(', ', $this->getIdentity()->getRoles());

            // Reset context to avoid side-effect on next usage of $this->isGranted()
            if ($user instanceof \Application\Model\User) {
                $user->resetRolesContext();
            }

            $contextMessage = $context ? 'in context "' . get_class($context) . '#' . $context->getId() . '"' : 'without any context';
            $this->message = 'Insufficient access rights for permission "' . $permission . '" on "' . get_class($object) . '#' . $object->getId() . '" with your current roles [' . $roles . '] ' . $contextMessage;
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

    /**
     * Returns an assertion if necessary
     * @param \Application\Model\AbstractModel $object
     * @param string $action
     * @return \Application\Assertion\AbstractAssertion|null
     */
    protected function getAssertion(AbstractModel $object, $action)
    {
        // Every action which is not read on answer must check if questionnaire status is not VALIDATED
        if ($object instanceof \Application\Model\Answer && $action != 'read') {
            return new \Application\Assertion\CanAnswerQuestionnaire($object);
        }

        return null;
    }

}
