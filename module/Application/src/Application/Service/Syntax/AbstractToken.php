<?php

namespace Application\Service\Syntax;

abstract class AbstractToken
{

    /**
     * Return the regexp pattern used for this syntax
     * @return string
     */
    abstract public function getPattern();

    /**
     * Returns an array representing the token
     * @param array $matches
     * @param \Application\Service\Syntax\Parser $parser
     * @return array
     */
    abstract public function getStructure(array $matches, Parser $parser);
}
