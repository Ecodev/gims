<?php

namespace Application\Assertion;

use ZfcRbac\Service\AuthorizationService;

/**
 * Abstract class to handle message in case of failed assertion
 */
abstract class AbstractAssertion implements \ZfcRbac\Assertion\AssertionInterface
{

    /**
     * Explanation message in case of false assertion
     * @var string
     */
    private $message = null;

    /**
     * Dynamic assertion.
     *
     * @param \ZfcRbac\Service\AuthorizationService $authorizationService
     * @return boolean
     */
    public function assert(AuthorizationService $authorizationService)
    {
        $result = $this->internalAssert($authorizationService);
        $this->message = $result ? null : $this->getInternalMessage();

        return $result;
    }

    /**
     * If the last assertion failed, return an explanation message, otherwise null
     *
     * @return string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns the message explaining the (failing) assertion for end-user
     * @return string
     */
    abstract protected function getInternalMessage();

    /**
     * Returns whether the assertion is true
     *
     * @param \ZfcRbac\Service\AuthorizationService $authorizationService
     * @return boolean
     */
    abstract protected function internalAssert(AuthorizationService $authorizationService);
}
