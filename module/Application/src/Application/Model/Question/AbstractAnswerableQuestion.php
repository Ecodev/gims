<?php

namespace Application\Model\Question;

use Doctrine\ORM\Mapping as ORM;
use Application\Model\Answer;
use Application\Model\Filter;
use Doctrine\Common\Collections\Criteria;

/**
 * A question which can be answered by end-user, and thus may be specific to parts.
 * @ORM\Entity(repositoryClass="Application\Repository\QuestionRepository")
 */
abstract class AbstractAnswerableQuestion extends AbstractQuestion
{

    /**
     * @var Filter
     * @ORM\ManyToOne(targetEntity="Application\Model\Filter", fetch="EAGER", inversedBy="questions")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $filter;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="Application\Model\Answer", mappedBy="question")
     */
    private $answers;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\ManyToMany(targetEntity="Application\Model\Part")
     * @ORM\JoinTable(name="question_part", joinColumns={@ORM\JoinColumn(name="question_id", onDelete="CASCADE")})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $parts;

    /**
     * @var int
     * @ORM\Column(type="boolean", nullable=false, options={"default" = TRUE})
     */
    private $isCompulsory = false;

    /**
     * Constructor
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->parts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set filter
     * @param Filter $filter
     * @return self
     */
    public function setFilter(Filter $filter = null)
    {
        if ($this->filter) {
            $this->filter->removeQuestion($this);
        }

        $this->filter = $filter;

        if ($filter) {
            $filter->addQuestion($this);
        }

        return $this;
    }

    /**
     * Get filter
     * @return Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Get all answers
     * @param \Application\Model\Questionnaire $questionnaire
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAnswers(\Application\Model\Questionnaire $questionnaire = null)
    {
        if ($questionnaire) {
            $criteria = Criteria::create()->where(Criteria::expr()->eq("questionnaire", $questionnaire));
            $answers = $this->answers->matching($criteria);
        } else {
            $answers = $this->answers;
        }

        return $answers;
    }

    /**
     * Notify the question that it was added to the answer.
     * This should only be called by Answer::setQuestion()
     * @param Answer $answer
     * @return self
     */
    public function answerAdded(Answer $answer)
    {
        $this->getAnswers()->add($answer);

        return $this;
    }

    /**
     * Returns whether this question must be answered
     * @return boolean
     */
    public function isCompulsory()
    {
        return $this->isCompulsory;
    }

    /**
     * @param boolean $isCompulsory
     * @return self
     */
    public function setIsCompulsory($isCompulsory)
    {
        $this->isCompulsory = (bool) $isCompulsory;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getParts()
    {
        return $this->parts;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $parts
     * @return self
     */
    public function setParts(\Doctrine\Common\Collections\ArrayCollection $parts)
    {
        $this->parts = $parts;

        return $this;
    }

}
