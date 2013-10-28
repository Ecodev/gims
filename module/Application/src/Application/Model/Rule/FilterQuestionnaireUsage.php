<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * FilterQuestionnaireUsage allows us to "apply" a rule to a filter-questionnaire-part triple.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\Rule\FilterQuestionnaireUsageRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="filter_questionnaire_usage_unique",columns={"filter_id", "questionnaire_id", "part_id", "rule_id"})})
 */
class FilterQuestionnaireUsage extends AbstractQuestionnaireUsage
{

    /**
     * @var Filter
     *
     * @ORM\ManyToOne(targetEntity="Application\Model\Filter", inversedBy="filterQuestionnaireUsages")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $filter;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default" = 0})
     */
    private $isSecondLevel = false;

    /**
     * Set filter
     *
     * @param Filter $filter
     * @return FilterQuestionnaireUsage
     */
    public function setFilter(\Application\Model\Filter $filter)
    {
        $this->filter = $filter;
        $filter->filterQuestionnaireUsageAdded($this);

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
     * Set whether this rule is used in the second level of computation
     * @return boolean
     */
    public function setIsSecondLevel($isSecondLevel)
    {
        $this->isSecondLevel = $isSecondLevel;

        return $this;
    }

    /**
     * Returns whether this rule is used in the second level of computation
     * @return boolean
     */
    public function isSecondLevel()
    {
        return (bool) $this->isSecondLevel;
    }

}
