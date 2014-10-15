<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;

/**
 * QuestionnaireUsage allows us to "apply" a formula to a questionnaire-part pair. This
 * is used for what is called Calculations, Estimates and Ratios in original Excel files.
 * @ORM\Entity(repositoryClass="Application\Repository\Rule\QuestionnaireUsageRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="questionnaire_usage_unique",columns={"questionnaire_id", "part_id", "rule_id"})})
 * @ORM\HasLifecycleCallbacks
 */
class QuestionnaireUsage extends AbstractQuestionnaireUsage
{

    /**
     * @var Rule
     * @ORM\ManyToOne(targetEntity="Rule", inversedBy="questionnaireUsages"))
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    protected $rule;

    /**
     * @var Questionnaire
     * @ORM\ManyToOne(targetEntity="Application\Model\Questionnaire", inversedBy="questionnaireUsages"))
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    protected $questionnaire;

    /**
     * Set questionnaire
     * @param \Application\Model\Questionnaire $questionnaire
     * @return self
     */
    public function setQuestionnaire(\Application\Model\Questionnaire $questionnaire)
    {
        parent::setQuestionnaire($questionnaire);
        $this->getQuestionnaire()->questionnaireUsageAdded($this);

        return $this;
    }

    /**
     * Get Filter, always return null
     * @return null
     */
    public function getFilter()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'questionnaire',
        ));
    }

    /**
     * Automatically called by Doctrine when the object is modified whatsoever to invalid computing cache
     * @ORM\PostPersist
     * @ORM\PreUpdate
     * @ORM\PreRemove
     */
    public function invalidateCache()
    {
        $cache = \Application\Module::getServiceManager()->get('Cache\Computing');
        $key = $this->getCacheKey();
        $cache->removeItem($key);
    }

    public function getCacheKey()
    {
        return 'qu:' . $this->getId();
    }

}
