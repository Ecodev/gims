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
     * @ORM\OneToMany(targetEntity="Application\Model\Question\AbstractQuestion", mappedBy="survey")
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
     * @ORM\Column(type="boolean", nullable=false, options={"default" = 0})
     */
    private $isActive = false;

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
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->setName($name);
        $this->questions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->questionnaires = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'name',
            'code',
            'isActive',
            'year',
            'dateStart',
            'dateEnd',
        ));
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
     * @param boolean $isActive
     *
     * @return Survey
     */
    public function setIsActive($isActive)
    {
        $this->isActive = (bool) $isActive;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
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
        return is_null($this->year) ? null : (int) $this->year;
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
     * Notify the survey that it was added to the question.
     * This should only be called by Question::setSurvey()
     *
     * @param \Application\Model\Question\AbstractQuestion $question
     *
     * @return Survey
     */
    public function questionAdded(\Application\Model\Question\AbstractQuestion $question)
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
     * Notify the survey that it was added to the questionnaire.
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

    /**
     * @inheritdoc
     */
    public function getRoleContext($action)
    {
        // If we don't have ID, that mean we were not saved yet,
        // and we cannot use ourself as context
        if ($this->getId()) {
            return $this;
        } else {
            return null;
        }
    }

}
