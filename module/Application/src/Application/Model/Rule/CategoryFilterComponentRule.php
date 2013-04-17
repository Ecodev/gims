<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * CategoryFilterComponentRule is used to link a CategoryFilterComponentRule, a Rule and a Questionnaire.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryFilterComponentRuleRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="rule_unique",columns={"category_filter_component_id", "questionnaire_id", "part_id"})})
 */
class CategoryFilterComponentRule extends \Application\Model\AbstractModel
{

    /**
     * @var CategoryFilterComponent
     *
     * @ORM\ManyToOne(targetEntity="Application\Model\CategoryFilterComponent", inversedBy="categoryFilterComponentRules")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $categoryFilterComponent;

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
     *   @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $rule;

    /**
     * Set categoryfiltercomponent
     *
     * @param CategoryFilterComponent $categoryfiltercomponent
     * @return CategoryFilterComponentRule
     */
    public function setCategoryFilterComponent(\Application\Model\CategoryFilterComponent $categoryfiltercomponent)
    {
        $this->categoryFilterComponent = $categoryfiltercomponent;
        $categoryfiltercomponent->ruleAdded($this);

        return $this;
    }

    /**
     * Get categoryFilterComponent
     *
     * @return \Application\Model\CategoryFilterComponent
     */
    public function getCategoryFilterComponent()
    {
        return $this->categoryFilterComponent;
    }

    /**
     * Set questionnaire
     *
     * @param \Application\Model\Questionnaire $questionnaire
     * @return CategoryFilterComponentRule
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
     * @return CategoryFilterComponentRule
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
     * @return CategoryFilterComponentRule
     */
    public function setRule(AbstractRule $rule = null)
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
