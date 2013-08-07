<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Question defines a Survey (and NOT a questionnaire).
 *
 * @ORM\Entity(repositoryClass="Application\Repository\QuestionRepository")
 */
class Question extends AbstractModel
{

    /**
     * @var array
     */
    protected static $jsonConfig = array(
        'name',
        'sorting',
    );

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    private $sorting;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $name;

    /**
     * @var Filter
     *
     * @ORM\ManyToOne(targetEntity="Filter", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="SET NULL", nullable=false)
     * })
     */
    private $filter;

    /**
     * @var Question
     *
     * @ORM\ManyToOne(targetEntity="Question")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="SET NULL")
     * })
     */
    private $officialQuestion;

    /**
     * @var Question
     *
     * @ORM\ManyToOne(targetEntity="Question")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="SET NULL")
     * })
     */
    private $parent;

    /**
     * @var Survey
     *
     * @ORM\ManyToOne(targetEntity="Survey", inversedBy="questions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $survey;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="question")
     */
    private $answers;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="QuestionChoice", mappedBy="question")
     * @ORM\JoinColumns({
     *  @ORM\JoinColumn(onDelete="CASCADE", nullable=true)
     * })
     */
    private $choices;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\ManyToMany(targetEntity="Part")
     * })
     */
    private $parts;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $compulsory;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $info;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->choices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->parts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set sorting
     *
     * @param integer $sorting
     * @return Question
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * Get sorting
     *
     * @return integer
     */
    public function getSorting()
    {
        return (int) $this->sorting;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Question
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Question
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set filter
     *
     * @param Filter $filter
     * @return Question
     */
    public function setFilter(Filter $filter = null)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Get filter
     *
     * @return Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set officialQuestion
     *
     * @param Question $officialQuestion
     * @return Question
     */
    public function setOfficialQuestion(Question $officialQuestion = null)
    {
        $this->officialQuestion = $officialQuestion;

        return $this;
    }

    /**
     * Get officialQuestion
     *
     * @return Question
     */
    public function getOfficialQuestion()
    {
        return $this->officialQuestion;
    }

    /**
     * Set parent
     *
     * @param Question $parent
     * @return Question
     */
    public function setParent(Question $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Question
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set survey
     *
     * @param Survey $survey
     * @return Question
     */
    public function setSurvey(Survey $survey)
    {
        $this->survey = $survey;

        $this->survey->questionAdded($this);

        return $this;
    }

    /**
     * Get survey
     *
     * @return Survey
     */
    public function getSurvey()
    {
        return $this->survey;
    }

    /**
     * Get all answers
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Notify the question that it was added to the answer.
     * This should only be called by Answer::setQuestion()
     *
     * @param Answer $answer
     *
     * @return Question
     */
    public function answerAdded(Answer $answer)
    {
        $this->getAnswers()->add($answer);

        return $this;
    }

    /**
     * @return int
     */
    public function getCompulsory()
    {
        return $this->compulsory;
    }

    /**
     * @param int $compulsory
     * @return $this
     */
    public function setCompulsory($compulsory)
    {
        $this->compulsory = $compulsory;
        return $this;
    }

    /**
     * @return string
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param string $info
     * @return $this
     */
    public function setInfo($info)
    {
        $this->info = $info;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * Set new choices, replacing entirely existing choices
     * @param \Doctrine\Common\Collections\ArrayCollection $choices
     * @return $this
     */
    public function setChoices(\Doctrine\Common\Collections\ArrayCollection $choices)
    {
        // Affect this question to each choices given, which will automatically add themselve to our collection
        foreach ($choices as $choice) {
            $choice->setQuestion($this);
        }

        // Clean up the collection from old choices
        foreach ($this->getChoices() as $choice) {
            if (!$choices->contains($choice)) {
                $this->getChoices()->removeElement($choice);
                \Application\Module::getEntityManager()->remove($choice);
            }
        }

        return $this;
    }

    /**
     * Notify the question that it was added to the choice.
     * This should only be called by QuestionChoice::setQuestion()
     *
     * @param QuestionChoice $questionChoice
     *
     * @return Question
     */
    public function questionChoiceAdded(QuestionChoice $questionChoice)
    {
        if (!$this->getChoices()->contains($questionChoice)) {
            $this->getChoices()->add($questionChoice);
        }

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
     * @return $this
     */
    public function setParts(\Doctrine\Common\Collections\ArrayCollection $parts)
    {
        //if(!$this->getParts()->contains($part))
        //	$this->getParts()->add($part);
        $this->parts = $parts;
        return $this;
    }

}
