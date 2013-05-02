<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rule is a way to exclude specify custom behavior during computing of filter.
 * This is on a filter-questionnaire-part basis.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\RuleRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\Table(name="rule")
 */
abstract class AbstractRule extends \Application\Model\AbstractModel
{

    /**
     * Return the name of this rule (for end-user)
     * @return string
     */
    abstract public function getName();
}
