<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * FilterQuestionnaireUsage allows us to "apply" a rule to a filter-questionnaire-part triple.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\Rule\FilterQuestionnaireUsageRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="filter_questionnaire_usage_unique",columns={"filter_id", "questionnaire_id", "part_id", "rule_id", "is_second_step"})})
 * @ORM\HasLifecycleCallbacks
 */
class FilterQuestionnaireUsage extends AbstractQuestionnaireUsage
{

    /**
     * @var Rule
     *
     * @ORM\ManyToOne(targetEntity="Rule", inversedBy="filterQuestionnaireUsages"))
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    protected $rule;

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
    private $isSecondStep = false;

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), [
            'filter',
            'questionnaire',
        ]);
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
     * Set whether this rule is used in the second step of computation
     * @return boolean
     */
    public function setIsSecondStep($isSecondStep)
    {
        $this->isSecondStep = $isSecondStep;

        return $this;
    }

    /**
     * Returns whether this rule is used in the second step of computation
     * @return boolean
     */
    public function isSecondStep()
    {
        return (bool) $this->isSecondStep;
    }

    /**
     * Automatically called by Doctrine when the object is modified whatsoever to invalid computing cache
     * @ORM\PostPersist
     * @ORM\PreUpdate
     * @ORM\PreRemove
     */
    public function invalidateCache()
    {
        $cache = \Application\Module::getServiceManager()->get('Calculator\Cache');
        $key = 'F#' . $this->getFilter()->getId() . ',Q#' . $this->getQuestionnaire()->getId() . ',P#' . $this->getPart()->getId();
        $cache->removeItem($key);

        $key = $this->getCacheKey();
        $cache->removeItem($key);
    }

    public function getCacheKey()
    {
        return 'fqu:' . $this->getId();
    }

    public function getActivityData()
    {
        $data = parent::getActivityData();

        $data['filter'] = [
            'id' => $this->getFilter()->getId(),
            'name' => $this->getFilter()->getName(),
        ];

        $data['questionnaire'] = [
            'id' => $this->getQuestionnaire()->getId(),
            'name' => $this->getQuestionnaire()->getName(),
        ];

        return $data;
    }

}
