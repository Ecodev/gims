<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Question defines a Survey (and NOT a questionnaire).
 *
 * @ORM\Entity(repositoryClass="Application\Repository\QuestionRepository")
 */
class QuestionChoice extends AbstractModel
{
    /**
     * @var array
     */
    protected static $jsonConfig = array(
        'value',
        'label'
    );

    /**
     * @var Question
     *
     * @ORM\ManyToOne(targetEntity="Question", inversedBy="choices")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $question;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=4, scale=3, nullable=true)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $label;

    /**
     * Constructor
     */
    public function __construct()
    {
        #$this->answers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return \Application\Model\Question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param \Application\Model\Question $question
     * @return $this
     */
    public function setQuestion(Question $question)
    {
        $this->question = $question;
        $this->question->questionChoiceAdded($this);

        return $this;
    }

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }


}
