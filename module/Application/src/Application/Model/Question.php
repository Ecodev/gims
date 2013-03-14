<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Question
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
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Category")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    private $category;

    /**
     * @var Question
     *
     * @ORM\ManyToOne(targetEntity="Question")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    private $officialQuestion;

    /**
     * @var Question
     *
     * @ORM\ManyToOne(targetEntity="Question")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    private $parent;

    /**
     * @var Questionnaire
     *
     * @ORM\ManyToOne(targetEntity="Questionnaire")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $questionnaire;

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
     * Set category
     *
     * @param Category $category
     * @return Question
     */
    public function setCategory(Category $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return Category 
     */
    public function getCategory()
    {
        return $this->category;
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
     * Set questionnaire
     *
     * @param Questionnaire $questionnaire
     * @return Question
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

}
