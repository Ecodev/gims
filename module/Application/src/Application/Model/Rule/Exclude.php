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
    public function getName()
    {
        return 'Exclude from computing';
    }
}
