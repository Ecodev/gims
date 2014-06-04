<?php

namespace Application\Service\Syntax;

abstract class AbstractToken
{

    /**
     * Return the regexp pattern used for this syntax
     * @return string
     */
    abstract public function getPattern();
}
