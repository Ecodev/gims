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
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $hasParts = false;

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
        return $this->sorting;
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
     * Get hasParts
     *
     * @return boolean
     */
    public function getHasParts()
    {
        return $this->hasParts;
    }

    /**
     * Set hasParts
     *
     * @param boolean $hasParts
     * @return Role
     */
    public function setHasParts($hasParts)
    {
        $this->hasParts = $hasParts;

        return $this;
    }

}
