<?php

namespace Application\Model\Question;

use Doctrine\ORM\Mapping as ORM;
use Application\Model\Survey;

/**
 * Question defines a Survey (and NOT a questionnaire).
 *
 * @ORM\Entity(repositoryClass="Application\Repository\QuestionRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\Table(name="question")
 */
abstract class AbstractQuestion extends \Application\Model\AbstractModel
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
     * @ORM\Column(type="text", nullable=false)
     */
    private $name;

    /**
     * @var Chapter
     *
     * @ORM\ManyToOne(targetEntity="Chapter", inversedBy="questions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="SET NULL")
     * })
     */
    private $chapter;

    /**
     * @var Survey
     *
     * @ORM\ManyToOne(targetEntity="Application\Model\Survey", inversedBy="questions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $survey;

    /**
     * Constructor
     */
    public function __construct($name = null)
    {
        $this->setName($name);
    }

    /**
     * @inheritdoc
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'name',
            'sorting',
        ));
    }

    /**
     * Set sorting
     *
     * @param integer $sorting
     * @return AbstractQuestion
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
     * Get question type.
     *
     * @return QuestionType
     */
    public function getType()
    {
        return \Application\Model\QuestionType::getType(get_called_class());
    }

    /**
     * Set name
     *
     * @param string $name
     * @return AbstractQuestion
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
     * Set chapter
     *
     * @param Chapter $chapter
     * @return AbstractQuestion
     */
    public function setChapter(Chapter $chapter = null)
    {
        $this->chapter = $chapter;
        if ($chapter) {
            $chapter->questionAdded($this);
        }

        return $this;
    }

    /**
     * Get chapter
     *
     * @return Chapter
     */
    public function getChapter()
    {
        return $this->chapter;
    }

    /**
     * Set survey
     *
     * @param Survey $survey
     * @return AbstractQuestion
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
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getSiblings()
    {
        if (!is_null($this->getChapter())) {
            return $this->getChapter()->getQuestions();
        } else {
            $questions = $this->getSurvey()->getQuestions();

            $finalQuestions = new \Doctrine\Common\Collections\ArrayCollection();
            foreach ($questions as $question)
                if (!$question->getChapter())
                    $finalQuestions->add($question);

            return $finalQuestions;
        }
    }

    /**
     * @inheritdoc
     */
    public function getRoleContext($action)
    {
        return $this->getSurvey();
    }

}
