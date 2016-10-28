<?php

namespace Application\Model\Rule;

/**
 * Interface to identify classes that can be referenced in Rule formulas
 */
interface ReferencableInterface
{

    public function getId();

    /**
     * Return the single character used to identify the object type in a formula
     */
    public function getSymbol();
}
