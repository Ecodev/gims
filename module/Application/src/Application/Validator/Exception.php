<?php

namespace Application\Validator;

/**
 * Exception to be thrown when validation fails
 */
class Exception extends \Exception
{

    /**
     * @var array
     */
    private $messages;

    public function __construct(array $messages)
    {
        $this->messages = array_values($messages);
        parent::__construct(implode(', ', $messages));
    }

    public function getMessages()
    {
        return $this->messages;
    }

}
