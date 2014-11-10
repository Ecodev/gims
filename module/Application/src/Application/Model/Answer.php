<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Answer is the raw data on which all computing/graph/table are based.
 * @ORM\Entity(repositoryClass="Application\Repository\AnswerRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Answer extends AbstractModel
{

    /**
     * @var Choice
     * @ORM\ManyToOne(targetEntity="Application\Model\Question\Choice", inversedBy="answers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     * })
     */
    private $valueChoice;

    /**
     * @var float
     * @ORM\Column(type="decimal", precision=7, scale=6, nullable=true)
     */
    private $valuePercent;

    /**
     * @var float
     * @ORM\Column(type="float", nullable=true)
     */
    private $valueAbsolute;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $valueText;

    /**
     * Quality of the facilities.
     *
     * @var float
     * @ORM\Column(type="decimal", precision=3, scale=2, nullable=false, options={"default" = 1} )
     */
    private $quality = 1;

    /**
     * @var \Application\Model\Question\AbstractAnswerableQuestion
     * @ORM\ManyToOne(targetEntity="Application\Model\Question\AbstractAnswerableQuestion", inversedBy="answers", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $question;

    /**
     * @var Questionnaire
     * @ORM\ManyToOne(targetEntity="Questionnaire", inversedBy="answers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $questionnaire;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $valueUser;

    /**
     * @var Part
     * @ORM\ManyToOne(targetEntity="Part")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $part;

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'valuePercent',
            'valueAbsolute',
            'valueText',
            'isCheckboxChecked',
            'valueChoice',
            'valueUser'
        ));
    }

    /**
     * Set valueChoice
     * @param Application\Model\Question\Choice
     * @return self
     */
    public function setValueChoice(\Application\Model\Question\Choice $valueChoice = null)
    {
        $this->valueChoice = $valueChoice;

        return $this;
    }

    /**
     * Get valueChoice
     * @return Choice
     */
    public function getValueChoice()
    {
        return $this->valueChoice;
    }

    /**
     * Set valuePercent (between 0.0 and 1.0)
     * @param float $valuePercent
     * @return self
     */
    public function setValuePercent($valuePercent)
    {
        $this->valuePercent = $valuePercent;

        return $this;
    }

    /**
     * Get valuePercent (between 0.0 and 1.0)
     * @return float
     */
    public function getValuePercent()
    {
        return is_null($this->valuePercent) ? null : (float) $this->valuePercent;
    }

    /**
     * Set valueAbsolute
     * @param float $valueAbsolute
     * @return self
     */
    public function setValueAbsolute($valueAbsolute)
    {
        $this->valueAbsolute = $valueAbsolute;

        return $this;
    }

    /**
     * Get valueAbsolute
     * @return float
     */
    public function getValueAbsolute()
    {
        return $this->valueAbsolute;
    }

    /**
     * Set valueText
     * @param string $valueText
     * @return self
     */
    public function setValueText($valueText)
    {
        $this->valueText = $valueText;

        return $this;
    }

    /**
     * Get valueText
     * @return string
     */
    public function getValueText()
    {
        return $this->valueText;
    }

    /**
     * Set quality (between 0.0 and 1.0)
     * @param float $quality
     * @return self
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * Get quality (between 0.0 and 1.0)
     * @return float
     */
    public function getQuality()
    {
        return (float) $this->quality;
    }

    /**
     * Set question
     * @param \Application\Model\Question\AbstractAnswerableQuestion $question
     * @return self
     */
    public function setQuestion(\Application\Model\Question\AbstractAnswerableQuestion $question)
    {
        $this->question = $question;

        $this->question->answerAdded($this);

        return $this;
    }

    /**
     * Get question
     * @return \Application\Model\Question\AbstractAnswerableQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set questionnaire
     * @param Questionnaire $questionnaire
     * @return self
     */
    public function setQuestionnaire(Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;
        $questionnaire->answerAdded($this);

        return $this;
    }

    /**
     * Get questionnaire
     * @return Questionnaire
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * Set valueUser
     * @param User $valueUser
     * @return self
     */
    public function setValueUser(User $valueUser = null)
    {
        $this->valueUser = $valueUser;

        return $this;
    }

    /**
     * Get valueUser
     * @return User
     */
    public function getValueUser()
    {
        return $this->valueUser;
    }

    /**
     * Set part
     * @param Part $part
     * @return self
     */
    public function setPart(Part $part)
    {
        $this->part = $part;

        return $this;
    }

    /**
     * Get part
     * @return Part
     */
    public function getPart()
    {
        return $this->part;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleContext($action)
    {
        return $this->getQuestionnaire();
    }

    public function getName()
    {
        return '';
    }

    /**
     * Automatically called by Doctrine when the object is modified whatsoever to invalid computing cache
     * @ORM\PostPersist
     * @ORM\PreUpdate
     * @ORM\PreRemove
     */
    public function invalidateCache()
    {
        $key = 'F#' . $this->getQuestion()->getFilter()->getId() . ',Q#' . $this->getQuestionnaire()->getId() . ',P#' . $this->getPart()->getId();
        $cache = \Application\Module::getServiceManager()->get('Calculator\Cache');
        $cache->removeItem($key);
    }

}
