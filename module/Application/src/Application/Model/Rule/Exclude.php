<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * Exclude is a way to exclude some data from computing.
 *
 * This entity is special, and there should be ONLY ONE in the entire system
 * @see RuleRepository::getSingletonExclude()
 *
 * @ORM\Entity
 */
class Exclude extends AbstractRule
{
    private $readonlyName = 'Exclude from computing';

    public function __construct()
    {
        // Define once the read-only name
        parent::setName($this->readonlyName);
    }

    /**
     * Forbids changing name. Will always raise exception
     * @param string $name
     * @throws \Exception
     */
    public function setName($name)
    {
        throw new \Exception('Exclude rule name is readonly and cannot be changed by end-user');
    }
}
