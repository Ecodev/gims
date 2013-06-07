<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Questionnaire is a particular "instance" of a Survey for a specific country (or
 * more generally for a geographic location). So a questionnaire links questions to
 * a country and its answers.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\QuestionnaireRepository")
 */
class Questionnaire extends AbstractModel implements \Application\Service\RoleContextInterface
{
    /**
     * @var array
     */
    protected static $jsonConfig
        = array(
            'name',
            'dateObservationStart',
            'dateObservationEnd',
            'completed',
            'spatial',
            'comments',
            'status',
        );

    /**
     * @var array
     */
    protected static $relationProperties
        = array(
            'survey' => '\Application\Model\Survey',
            'geoname' => '\Application\Model\Geoname',
            'answers' => '\Application\Model\Answer',
        );

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetimetz", nullable=false)
     */
    private $dateObservationStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetimetz", nullable=false)
     */
    private $dateObservationEnd;

    /**
     * @var Geoname
     *
     * @ORM\ManyToOne(targetEntity="Geoname")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="SET NULL", nullable=false)
     * })
     */
    private $geoname;

    /**
     * @var Survey
     *
     * @ORM\ManyToOne(targetEntity="Survey", inversedBy="questionnaires")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $survey;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="questionnaire")
     */
    private $answers;

    /**
     * @var QuestionnaireStatus
     *
     * @ORM\Column(type="questionnaire_status")
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $comments;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->status = QuestionnaireStatus::$NEW;
    }

    /**
     * Set dateObservationStart
     *
     * @param \DateTime $dateObservationStart
     * @return Questionnaire
     */
    public function setDateObservationStart(\DateTime $dateObservationStart)
    {
        $this->dateObservationStart = $dateObservationStart;

        return $this;
    }

    /**
     * Get dateObservationStart
     *
     * @return \DateTime
     */
    public function getDateObservationStart()
    {
        return $this->dateObservationStart;
    }

    /**
     * Set dateObservationEnd
     *
     * @param \DateTime $dateObservationEnd
     * @return Questionnaire
     */
    public function setDateObservationEnd(\DateTime $dateObservationEnd)
    {
        $this->dateObservationEnd = $dateObservationEnd;

        return $this;
    }

    /**
     * Get dateObservationEnd
     *
     * @return \DateTime
     */
    public function getDateObservationEnd()
    {
        return $this->dateObservationEnd;
    }

    /**
     * Set geoname
     *
     * @param Geoname $geoname
     * @return Questionnaire
     */
    public function setGeoname(Geoname $geoname)
    {
        $this->geoname = $geoname;

        return $this;
    }

    /**
     * Get geoname
     *
     * @return Geoname
     */
    public function getGeoname()
    {
        return $this->geoname;
    }

    /**
     * Set survey
     *
     * @param Survey $survey
     * @return Questionnaire
     */
    public function setSurvey(Survey $survey)
    {
        $this->survey = $survey;

        $this->survey->questionnaireAdded($this);

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
     * Get answers
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Answer
     */
    public function setStatus(QuestionnaireStatus $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return QuestionnaireStatus
     */
    public function getStatus()
    {
        // cast value
        return $this->status;
    }

    /**
     * Notify the questionnaire that he has a new answer.
     * This should only be called by Answer::setQuestionnaire()
     * @param Answer $answer
     * @return Questionnaire
     */
    public function answerAdded(Answer $answer)
    {
        $this->getAnswers()->add($answer);

        return $this;
    }

    /**
     * Return the computed name based on geoname and survey
     * @return string
     */
    public function getName()
    {
        return $this->getSurvey()->getCode() . ' - ' . $this->getGeoname()->getName();
    }

    /**
     * Return the computed spatial name
     *
     * @return string
     */
    public function getSpatial()
    {
        return $this->getGeoname()->getName();
    }

    /**
     * Return percentage of answered questions
     *
     * @return string
     */
    public function getCompleted()
    {
        $result = 0;
        if (!$this->getAnswers()->isEmpty()) {

            $questionCount = 0;
            foreach ($this->getSurvey()->getQuestions() as $q) {
                if ($q->getHasParts())
                    $questionCount += 3; // TODO: get actual parts count instead of hardcoded
                else
                    $questionCount += 1;
            }
            $questionCount / $this->getAnswers()->count();
        }

        return $result;
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

}
