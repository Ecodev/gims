<?php

namespace Application\Service;

use Application\Model\AbstractModel;
use Application\Assertion\AbstractAssertion;

/**
 * This class allow us to query permission model and find out if the current
 * user is allowed to do things.
 * The main method is <code>isActionGranted()</code> and should be used for most
 * cases. It automates almost everything for the most common cases.
 * Then, lower-level API, <code>isGrantedWithContext()</code> and
 * <code>isGranted()</code> can be used for more specific cases.
 */
class AuthorizationService extends \ZfcRbac\Service\AuthorizationService
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
        $permission = \Application\Model\Permission::getPermissionName($object, $action);
        $context = $object->getRoleContext($action);
        $assertion = $this->getAssertion($object, $action);

        if ($context) {
            $result = $this->isGrantedWithContext($context, $permission, $assertion);
        } elseif ($this->getIdentity() && $object->getCreator() === $this->getIdentity()) {
            $result = true;
        } else {
            $result = $this->isGranted($permission, $assertion);
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
            $this->generateMessage($object, $context, $permission);
        }
    }

    /**
     * Generate the message explaining why the permission was denied
     * @param AbstractModel $object
     * @param RoleContextInterface $context
     * @param string $permission
     */
    private function generateMessage(AbstractModel $object, RoleContextInterface $context = null, $permission)
    {
        $user = $this->getIdentity();
        $roles = 'anonymous';
        if ($user instanceof \Application\Model\User && $context) {
            $user->setRolesContext($context);
            $roles = implode(', ', $user->getRoles());
            $user->resetRolesContext();
        }

        $contextMessages = $this->getContextMessages($context);

        $name = is_callable(array($object, 'getName')) ? ' (' . $object->getName() . ')' : '';
        $this->message = 'Insufficient access rights for permission "' . $permission . '" on "' . get_class($object) . '#' . $object->getId() . $name . '" with your current roles [' . $roles . '] ' . $contextMessages;
    }

    /**
     * Return a string explaining the contexts
     * @param \Application\Service\RoleContextInterface $context
     * @return string
     */
    private function getContextMessages(RoleContextInterface $context = null)
    {
        if (is_null($context)) {
            return 'without any context';
        } elseif (!$context instanceof \Traversable) {
            $context = [$context];
        }

        $contextMessages = [];
        foreach ($context as $singleContext) {
            $contextId = $singleContext->getId() ? '#' . $singleContext->getId() : '#null';
            $contextMessages[] = '"' . get_class($singleContext) . $contextId . '" (' . $singleContext->getName() . ')';
        }

        return 'with contexts ' . implode(' and ', $contextMessages);
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
     * Returns true if the user has the permission in the given context(s).
     * @param \Application\Service\RoleContextInterface $context (can pass an array)
     * @param string $permission
     * @param null|Closure|AssertionInterface $assert
     * @throws InvalidArgumentException
     * @return bool
     */
    public function isGrantedWithContext(RoleContextInterface $context, $permission, $assert = null)
    {
        $isGranted = false;

        if ($context instanceof \ArrayAccess) {
            foreach ($context as $singleContext) {
                $isGranted = $this->isGrantedWithSingleContext($singleContext, $permission, $assert);
                if ($context->getGrantOnlyIfGrantedByAllContexts() && !$isGranted) {
                    return $isGranted;
                }
            }
        } else {
            $isGranted = $this->isGrantedWithSingleContext($context, $permission, $assert);
        }

        return $isGranted;
    }

    /**
     * Returns true if the user has the permission in the given context.
     * @param \Application\Service\RoleContextInterface $context
     * @param string $permission
     * @param null|Closure|AssertionInterface $assert
     * @throws InvalidArgumentException
     * @return bool
     */
    private function isGrantedWithSingleContext(RoleContextInterface $context, $permission, $assert = null)
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
