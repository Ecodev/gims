<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * FilterRule allows us to "apply" a rule to a filter-questionnaire-part triple.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\FilterRuleRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="filter_rule_unique",columns={"filter_id", "questionnaire_id", "part_id", "rule_id"})})
 */
class FilterRule extends \Application\Model\AbstractModel
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
     * @var Questionnaire
     *
     * @ORM\ManyToOne(targetEntity="Application\Model\Questionnaire")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $questionnaire;

    /**
     * @var Part
     *
     * @ORM\ManyToOne(targetEntity="Application\Model\Part")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $part;

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
     * Set questionnaire
     *
     * @param \Application\Model\Questionnaire $questionnaire
     * @return FilterRule
     */
    public function setQuestionnaire(\Application\Model\Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;

        return $this;
    }

    /**
     * Get questionnaire
     *
     * @return \Application\Model\Questionnaire
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * Set part
     *
     * @param \Application\Model\Part $part
     * @return FilterRule
     */
    public function setPart(\Application\Model\Part $part = null)
    {
        $this->part = $part;

        return $this;
    }

    /**
     * Get part
     *
     * @return \Application\Model\Part
     */
    public function getPart()
    {
        return $this->part;
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

}
