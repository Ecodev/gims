<?php

namespace Application\Model\Question;

use Doctrine\ORM\Mapping as ORM;

/**
 * A Chapter is a very special kind of question which cannot be answered,
 * but can contains other questions (or chapter).
 *
 * @ORM\Entity(repositoryClass="Application\Repository\QuestionRepository")
 */
class Chapter extends AbstractQuestion
{

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $description = '';

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true, options={"default" = 0})
     */
    private $isFinal = false;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OrderBy({"sorting" = "ASC"})
     * @ORM\OneToMany(targetEntity="Application\Model\Question\AbstractQuestion", mappedBy="chapter")
     */
    private $questions;

    /**
     * Constructor
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->questions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set description
     *
     * @param string $description
     * @return AbstractQuestion
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
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
     * Notify the chapter that it was added to the question.
     * This should only be called by Question::setChapter()
     *
     * @param \Application\Model\Question\AbstractQuestion $question
     *
     * @return Chapter
     */
    public function questionAdded(\Application\Model\Question\AbstractQuestion $question)
    {
        $this->getQuestions()->add($question);

        return $this;
    }

    /**
     * Set final
     *
     * @param boolean $isFinal
     * @return AbstractQuestion
     */
    public function setIsFinal($isFinal)
    {
        $this->isFinal = (bool) $isFinal;

        return $this;
    }

    /**
     * Get final
     *
     * @return string
     */
    public function isFinal()
    {
        return $this->isFinal;
    }

}
