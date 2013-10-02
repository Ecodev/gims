<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * FilterRule allows us to "apply" a rule to a filter-questionnaire-part triple.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\Rule\FilterRuleRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="filter_rule_unique",columns={"filter_id", "questionnaire_id", "part_id", "rule_id"})})
 */
class FilterRule extends AbstractFormulaUsage
{

    /**
     * @var Filter
     *
     * @ORM\ManyToOne(targetEntity="Application\Model\Filter", inversedBy="filterRules")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $filter;

    /**
     * @var AbstractRule
     *
     * @ORM\ManyToOne(targetEntity="AbstractRule")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $rule;

    /**
     * Set filter
     *
     * @param Filter $filter
     * @return FilterRule
     */
    public function setFilter(\Application\Model\Filter $filter)
    {
        $this->filter = $filter;
        $filter->ruleAdded($this);

        return $this;
    }

    /**
     * Get filter
     *
     * @return \Application\Model\Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set rule
     *
     * @param AbstractRule $rule
     * @return FilterRule
     */
    public function setRule(AbstractRule $rule)
    {
        $this->rule = $rule;

        return $this;
    }

    /**
     * Get rule
     *
     * @return AbstractRule
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Get Formula
     *
     * @return Formula|null
     */
    public function getFormula()
    {
        return $this->getRule() instanceof Formula ? $this->getRule() : null;
    }

}
