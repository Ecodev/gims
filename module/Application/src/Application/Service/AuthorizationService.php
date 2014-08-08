<?php

namespace Application\Service;

use Application\Model\AbstractModel;
use Application\Model\Questionnaire;
use Application\Model\QuestionnaireStatus;
use Application\Utility;

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
        $this->setCurrentAssertion($object, $permission, $action, $context);

        if ($action == 'read' && $object instanceof Questionnaire && $object->getStatus() == QuestionnaireStatus::$PUBLISHED) {

            // Anybody can read a published questionnaire
            $result = true;
        } elseif ($context) {
            $result = $this->isGrantedWithContext($context, $permission);
        } elseif ($this->getIdentity() && $object->getCreator() === $this->getIdentity()) {
            $result = true;
        } else {
            $result = $this->isGranted($permission);
        }

        $this->setMessage($result, $object, $permission, $context);

        return $result;
    }

    /**
     * Format a message in case of access denied
     * @param boolean $isGranted
     * @param AbstractModel $object
     * @param string $permission
     * @param RoleContextInterface $context
     */
    private function setMessage($isGranted, AbstractModel $object, $permission, RoleContextInterface $context = null)
    {
        $assertion = $this->getAssertion($permission);

        if ($isGranted) {
            $this->message = null;
        } elseif ($assertion && $assertion->getMessage()) {
            $this->message = $assertion->getMessage();
        } else {
            $this->generateMessage($object, $permission, $context);
        }
    }

    /**
     * Generate the message explaining why the permission was denied
     * @param AbstractModel $object
     * @param string $permission
     * @param RoleContextInterface $context
     */
    private function generateMessage(AbstractModel $object, $permission, RoleContextInterface $context = null)
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
        $this->message = 'Insufficient access rights for permission "' . $permission . '" on "' . Utility::getShortClassName($object) . '#' . $object->getId() . $name . '" with your current roles [' . $roles . '] ' . $contextMessages;
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

        $contexts = Utility::objectsToString($context);

        return 'in contexts [' . $contexts . ']';
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
     * @param \Application\Service\RoleContextInterface $context (can pass multiple roles with MultipleRoleContext)
     * @param string $permission
     * @throws InvalidArgumentException
     * @return bool
     */
    public function isGrantedWithContext(RoleContextInterface $context, $permission)
    {
        if ($context instanceof MultipleRoleContext) {
            $isGranted = true;
            foreach ($context as $singleContext) {
                $singleIsGranted = $this->isGrantedWithSingleContext($singleContext, $permission);

                // If at at least one grant is enough, then we can return early
                if ($singleIsGranted && !$context->getGrantOnlyIfGrantedByAllContexts()) {
                    return $singleIsGranted;
                }

                $isGranted = $isGranted && $singleIsGranted;
            }
        } else {
            $isGranted = $this->isGrantedWithSingleContext($context, $permission);
        }

        return $isGranted;
    }

    /**
     * Returns true if the user has the permission in the given context.
     * @param \Application\Service\RoleContextInterface $context
     * @param string $permission
     * @throws InvalidArgumentException
     * @return bool
     */
    private function isGrantedWithSingleContext(RoleContextInterface $context, $permission)
    {
        // Get the user to set the context for role
        $user = $this->getIdentity();
        if ($user instanceof \Application\Model\User) {
            $user->setRolesContext($context);
        }

        $result = $this->isGranted($permission);

        // Reset context to avoid side-effect on next usage of $this->isGranted()
        if ($user instanceof \Application\Model\User) {
            $user->resetRolesContext();
        }

        return $result;
    }

    /**
     * Get an assertion for given permission
     *
     * @param string|\Rbac\Permission\PermissionInterface $permission
     * @return \Application\Assertion\AbstractAssertion|null
     */
    public function getAssertion($permission)
    {
        if ($this->hasAssertion($permission)) {
            return $this->assertions[(string) $permission];
        } else {
            return null;
        }
    }

    /**
     * Set the assertion for the current $action
     * @param \Application\Model\AbstractModel $object
     * @param string $permission
     * @param string $action
     * @param RoleContextInterface $context
     */
    private function setCurrentAssertion(AbstractModel $object, $permission, $action, RoleContextInterface $context = null)
    {
        $assertion = null;

        // Every action which is not read on answer must check if questionnaire status is not VALIDATED
        if ($object instanceof \Application\Model\Answer && $action != 'read') {
            $assertion = new \Application\Assertion\CanAnswerQuestionnaire($object);
        } elseif ($object instanceof \Application\Model\AbstractUserRole && in_array($action, ['create', 'update'])) {
            $assertion = new \Application\Assertion\CanAttributeRole($object);
        } elseif ($object instanceof \Application\Model\Rule\Rule && $action != 'read') {
            $assertion = new \Application\Assertion\CanUpdateRule($object, $context);
        }

        $this->setAssertion($permission, $assertion);
    }

}
