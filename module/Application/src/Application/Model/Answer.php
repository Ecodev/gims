<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Answer is the raw data on which all computing/graph/table are based.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\AnswerRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="answer_unique",columns={"question_id", "questionnaire_id", "part_id"})})
 */
class Answer extends AbstractModel
{

    /**
     * @var array
     */
    protected static $jsonConfig = array(
        'valuePercent',
        'valueAbsolute',
    );

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $valueChoice;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=4, scale=3, nullable=true)
     */
    private $valuePercent;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $valueAbsolute;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $valueText;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=3, scale=2, nullable=true)
     */
    private $quality;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=3, scale=2, nullable=true)
     */
    private $relevance;

    /**
     * @var \Application\Model\Question\AbstractAnswerableQuestion
     *
     * @ORM\ManyToOne(targetEntity="Application\Model\Question\AbstractAnswerableQuestion", inversedBy="answers", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $question;

    /**
     * @var Questionnaire
     *
     * @ORM\ManyToOne(targetEntity="Questionnaire", inversedBy="answers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $questionnaire;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $valueUser;

    /**
     * @var Part
     *
     * @ORM\ManyToOne(targetEntity="Part")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $part;

    /**
     * Set valueChoice
     *
     * @param integer $valueChoice
     * @return Answer
     */
    public function setValueChoice($valueChoice)
    {
        $this->valueChoice = $valueChoice;

        return $this;
    }

    /**
     * Get valueChoice
     *
     * @return integer
     */
    public function getValueChoice()
    {
        return $this->valueChoice;
    }

    /**
     * Set valuePercent (between 0.0 and 1.0)
     *
     * @param float $valuePercent
     * @return Answer
     */
    public function setValuePercent($valuePercent)
    {
        $this->valuePercent = $valuePercent;

        return $this;
    }

    /**
     * Get valuePercent (between 0.0 and 1.0)
     *
     * @return float
     */
    public function getValuePercent()
    {
        return (float) $this->valuePercent;
    }

    /**
     * Set valueAbsolute
     *
     * @param float $valueAbsolute
     * @return Answer
     */
    public function setValueAbsolute($valueAbsolute)
    {
        $this->valueAbsolute = $valueAbsolute;

        return $this;
    }

    /**
     * Get valueAbsolute
     *
     * @return float
     */
    public function getValueAbsolute()
    {
        return $this->valueAbsolute;
    }

    /**
     * Set valueText
     *
     * @param string $valueText
     * @return Answer
     */
    public function setValueText($valueText)
    {
        $this->valueText = $valueText;

        return $this;
    }

    /**
     * Get valueText
     *
     * @return string
     */
    public function getValueText()
    {
        return $this->valueText;
    }

    /**
     * Set quality (between 0.0 and 1.0)
     *
     * @param float $quality
     * @return Answer
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * Get quality (between 0.0 and 1.0)
     *
     * @return float
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * Set relevance (between 0.0 and 1.0)
     *
     * @param float $relevance
     * @return Answer
     */
    public function setRelevance($relevance)
    {
        $this->relevance = $relevance;

        return $this;
    }

    /**
     * Get relevance (between 0.0 and 1.0)
     *
     * @return float
     */
    public function getRelevance()
    {
        return $this->relevance;
    }

    /**
     * Set question
     *
     * @param \Application\Model\Question\AbstractAnswerableQuestion $question
     * @return Answer
     */
    public function setQuestion(\Application\Model\Question\AbstractAnswerableQuestion $question)
    {
        $this->question = $question;

        $this->question->answerAdded($this);

        return $this;
    }

    /**
     * Get question
     *
     * @return \Application\Model\Question\AbstractAnswerableQuestion
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set questionnaire
     *
     * @param Questionnaire $questionnaire
     * @return Answer
     */
    public function setQuestionnaire(Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;
        $questionnaire->answerAdded($this);

        return $this;
    }

    /**
     * Get questionnaire
     *
     * @return Questionnaire
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * Set valueUser
     *
     * @param User $valueUser
     * @return Answer
     */
    public function setValueUser(User $valueUser = null)
    {
        $this->valueUser = $valueUser;

        return $this;
    }

    /**
     * Get valueUser
     *
     * @return User
     */
    public function getValueUser()
    {
        return $this->valueUser;
    }

    /**
     * Set part
     *
     * @param Part $part
     * @return Answer
     */
    public function setPart(Part $part)
    {
        $this->part = $part;

        return $this;
    }

    /**
     * Get part
     *
     * @return Part
     */
    public function getPart()
    {
        return $this->part;
    }

    /**
     * @inheritdoc
     */
    public function getRoleContext()
    {
        return $this->getQuestionnaire();
    }

}
