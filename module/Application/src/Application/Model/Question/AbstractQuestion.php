<?php

namespace Application\Model\Question;

use Doctrine\ORM\Mapping as ORM;
use Application\Model\Survey;
use Application\Model\Questionnaire;

/**
 * Question defines a Survey (and NOT a questionnaire).
 * @ORM\Entity(repositoryClass="Application\Repository\QuestionRepository")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\Table(name="question", uniqueConstraints={@ORM\UniqueConstraint(name="answerable_question_must_have_unique_filter_within_same_survey",columns={"survey_id", "filter_id"})})
 */
abstract class AbstractQuestion extends \Application\Model\AbstractModel
{

    /**
     * @var integer
     * @ORM\Column(type="smallint", nullable=false)
     */
    private $sorting = 1;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    private $name;

    /**
     * An array of alternate names: [questionnaireId => "my alternate name"]
     * @var array
     * @ORM\Column(type="json_array", nullable=false, options={"default" = "[]"})
     */
    private $alternateNames = array();

    /**
     * @var Chapter
     * @ORM\ManyToOne(targetEntity="Chapter", inversedBy="questions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     * })
     */
    private $chapter;

    /**
     * @var Survey
     * @ORM\ManyToOne(targetEntity="Application\Model\Survey", inversedBy="questions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $survey;

    /**
     * Constructor
     * @param string $name
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
     * @param integer $sorting
     * @return self
     */
    public function setSorting($sorting)
    {
        $this->sorting = $sorting;

        return $this;
    }

    /**
     * Get sorting
     * @return integer
     */
    public function getSorting()
    {
        return (int) $this->sorting;
    }

    /**
     * Get question type.
     * @return QuestionType
     */
    public function getType()
    {
        return \Application\Model\QuestionType::getType(get_called_class());
    }

    /**
     * Set name
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set all alternate names at once
     * @param array $alternateNames
     * @return self
     */
    public function setAlternameNames(array $alternateNames)
    {
        $this->alternateNames = $alternateNames;

        return $this;
    }

    /**
     * Get all alternate names
     * @return array
     */
    public function getAlternateNames()
    {
        return $this->alternateNames;
    }

    /**
     *
     * @param \Application\Model\Questionnaire $questionnaire
     * @param string $alternateName
     * @throws \Exception
     * @return self
     */
    public function addAlternateName(Questionnaire $questionnaire, $alternateName)
    {
        $id = $questionnaire->getId();
        if (!$id) {
            throw new \Exception('Questionnaire must have an ID');
        }

        $alternateNames = $this->getAlternateNames();
        $alternateNames[$id] = $alternateName;
        $this->setAlternameNames($alternateNames);

        return $this;
    }

    /**
     * Set chapter
     * @param Chapter $chapter
     * @return self
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
     * @return Chapter
     */
    public function getChapter()
    {
        return $this->chapter;
    }

    /**
     * Set survey
     * @param Survey $survey
     * @return self
     */
    public function setSurvey(Survey $survey)
    {
        $this->survey = $survey;

        $this->survey->questionAdded($this);

        return $this;
    }

    /**
     * Get survey
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
            foreach ($questions as $question) {
                if (!$question->getChapter()) {
                    $finalQuestions->add($question);
                }
            }

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
