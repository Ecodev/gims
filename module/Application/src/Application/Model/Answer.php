<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Answer
 *
 * @ORM\Entity(repositoryClass="Application\Repository\AnswerRepository")
 */
class Answer extends AbstractModel
{

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $valueChoice;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", nullable=true)
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
     * @ORM\Column(type="decimal", nullable=true)
     */
    private $quality;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", nullable=true)
     */
    private $relevance;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $status;

    /**
     * @var Question
     *
     * @ORM\ManyToOne(targetEntity="Question")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id")
     * })
     */
    private $question;

    /**
     * @var Questionnaire
     *
     * @ORM\ManyToOne(targetEntity="Questionnaire")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id")
     * })
     */
    private $questionnaire;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id")
     * })
     */
    private $valueUser;

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
     * Set valuePercent
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
     * Get valuePercent
     *
     * @return float 
     */
    public function getValuePercent()
    {
        return $this->valuePercent;
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
     * Set quality
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
     * Get quality
     *
     * @return float 
     */
    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * Set relevance
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
     * Get relevance
     *
     * @return float 
     */
    public function getRelevance()
    {
        return $this->relevance;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Answer
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set question
     *
     * @param Question $question
     * @return Answer
     */
    public function setQuestion(Question $question = null)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Get question
     *
     * @return Question 
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
    public function setQuestionnaire(Questionnaire $questionnaire = null)
    {
        $this->questionnaire = $questionnaire;

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

}
