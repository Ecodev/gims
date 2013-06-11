<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Survey is a campaign to gather data for a specific year, so it's a group of questions.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\SurveyRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="survey_code_unique",columns={"code"})})
 */
class Survey extends AbstractModel implements \Application\Service\RoleContextInterface
{
    /**
     * @var array
     */
    protected static $jsonConfig
        = array(
            'name',
            'code',
            'active',
            'year',
            'dateStart',
            'dateEnd',
        );

    /**
     * @var array
     */
    protected static $relationProperties
        = array(
            'questions' => '\Application\Model\Question',
            'questionnaires' => '\Application\Model\Questionnaire',
        );

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $code;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OrderBy({"sorting" = "ASC"})
     * @ORM\OneToMany(targetEntity="Question", mappedBy="survey")
     */
    private $questions;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="Questionnaire", mappedBy="survey")
     */
    private $questionnaires;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $active = false;

    /**
     * @var integer
     *
     * @ORM\Column(type="decimal", precision=4, scale=0, nullable=true)
     */
    private $year;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $comments;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $dateStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $dateEnd;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->questions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->questionnaires = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Survey
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
     * Set code
     *
     * @param string $code
     *
     * @return Survey
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Survey
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set year
     *
     * @param integer $year
     *
     * @return Survey
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer
     */
    public function getYear()
    {
        return (int)$this->year;
    }

    /**
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param string $comments
     *
     * @return Survey
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @param \DateTime $dateStart
     *
     * @return Survey
     */
    public function setDateStart(\DateTime $dateStart = null)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param \DateTime $dateEnd
     *
     * @return Survey
     */
    public function setDateEnd(\DateTime $dateEnd = null)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * Get questions
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * Notify the survey that he was added to the question.
     * This should only be called by Question::setSurvey()
     *
     * @param Question $question
     *
     * @return Survey
     */
    public function questionAdded(Question $question)
    {
        $this->getQuestions()->add($question);

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getQuestionnaires()
    {
        return $this->questionnaires;
    }

    /**
     * Notify the survey that he was added to the questionnaire.
     * This should only be called by Questionnaire::setSurvey()
     *
     * @param Questionnaire $questionnaire
     *
     * @return Survey
     */
    public function questionnaireAdded(Questionnaire $questionnaire)
    {
        $this->getQuestionnaires()->add($questionnaire);

        return $this;
    }
}
