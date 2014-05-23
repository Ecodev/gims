<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * FilterQuestionnaireUsage allows us to "apply" a rule to a filter-questionnaire-part triple.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\Rule\FilterQuestionnaireUsageRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="filter_questionnaire_usage_unique",columns={"filter_id", "questionnaire_id", "part_id", "rule_id", "is_second_level"})})
 */
class FilterQuestionnaireUsage extends AbstractQuestionnaireUsage
{

    /**
     * @var Questionnaire
     *
     * @ORM\ManyToOne(targetEntity="Application\Model\Questionnaire", inversedBy="filterQuestionnaireUsages"))
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    protected $questionnaire;

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
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'filter',
            'questionnaire',
        ));
    }

    /**
     * Set questionnaire
     *
     * @param \Application\Model\Questionnaire $questionnaire
     * @return QuestionnaireUsage
     */
    public function setQuestionnaire(\Application\Model\Questionnaire $questionnaire)
    {
        parent::setQuestionnaire($questionnaire);
        $questionnaire->filterQuestionnaireUsageAdded($this);

        return $this;
    }

    /**
     * Set filter
     *
     * @param Filter $filter
     * @return self
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
