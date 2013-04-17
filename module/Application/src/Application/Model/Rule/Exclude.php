<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * Exclusion is a way to exclude some data from computing
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
