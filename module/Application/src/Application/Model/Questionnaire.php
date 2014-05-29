<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;
use Application\Utility;

/**
 * Questionnaire is a particular "instance" of a Survey for a specific country (or
 * more generally for a geographic location). So a questionnaire links questions to
 * a country and its answers.
 * @ORM\Entity(repositoryClass="Application\Repository\QuestionnaireRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Questionnaire extends AbstractModel implements \Application\Service\RoleContextInterface
{

    private $originalStatus;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetimetz", nullable=false)
     */
    private $dateObservationStart;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetimetz", nullable=false)
     */
    private $dateObservationEnd;

    /**
     * @var Geoname
     * @ORM\ManyToOne(targetEntity="Geoname", inversedBy="questionnaires")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=false)
     * })
     */
    private $geoname;

    /**
     * @var Survey
     * @ORM\ManyToOne(targetEntity="Survey", inversedBy="questionnaires")
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $survey;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Answer", mappedBy="questionnaire")
     */
    private $answers;

    /**
     * @var QuestionnaireStatus
     * @ORM\Column(type="questionnaire_status")
     */
    private $status;

    /**
     * @var string
     * @ORM\Column(type="text", options={"default" = ""})
     */
    private $comments = '';

    /**
     * Additional formulas to compute interesting values which are not found in Filter tree
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\Application\Model\Rule\QuestionnaireUsage", mappedBy="questionnaire")
     * @ORM\OrderBy({"sorting" = "ASC", "id" = "ASC"})
     */
    private $questionnaireUsages;

    /**
     * Additional rules to apply to compute value
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="\Application\Model\Rule\FilterQuestionnaireUsage", mappedBy="questionnaire")
     * @ORM\OrderBy({"isSecondLevel" = "DESC", "sorting" = "ASC", "id" = "ASC"})
     */
    private $filterQuestionnaireUsages;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="Application\Model\Population", mappedBy="questionnaire")
     */
    private $populations;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->filterQuestionnaireUsages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->answers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->questionnaireUsages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->populations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setStatus(QuestionnaireStatus::$NEW);
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'name',
            'status'
        ));
    }

    /**
     * Set dateObservationStart
     * @param \DateTime $dateObservationStart
     * @return self
     */
    public function setDateObservationStart(\DateTime $dateObservationStart)
    {
        $this->dateObservationStart = $dateObservationStart;

        return $this;
    }

    /**
     * Get dateObservationStart
     * @return \DateTime
     */
    public function getDateObservationStart()
    {
        return $this->dateObservationStart;
    }

    /**
     * Set dateObservationEnd
     * @param \DateTime $dateObservationEnd
     * @return self
     */
    public function setDateObservationEnd(\DateTime $dateObservationEnd)
    {
        $this->dateObservationEnd = $dateObservationEnd;

        return $this;
    }

    /**
     * Get dateObservationEnd
     * @return \DateTime
     */
    public function getDateObservationEnd()
    {
        return $this->dateObservationEnd;
    }

    /**
     * Set geoname
     * @param Geoname $geoname
     * @return self
     */
    public function setGeoname(Geoname $geoname)
    {
        $this->geoname = $geoname;
        $geoname->questionnaireAdded($this);

        return $this;
    }

    /**
     * Get geoname
     * @return Geoname
     */
    public function getGeoname()
    {
        return $this->geoname;
    }

    /**
     * Set survey
     * @param Survey $survey
     * @return self
     */
    public function setSurvey(Survey $survey)
    {
        $this->survey = $survey;

        $this->survey->questionnaireAdded($this);

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
     * Get answers
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * Set status
     * @param string $status
     * @return Answer
     */
    public function setStatus(QuestionnaireStatus $status)
    {
        $this->originalStatus = $this->status;
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     * @return selfStatus
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
     * @return self
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
     * @return string
     */
    public function getSpatial()
    {
        return $this->getGeoname()->getName();
    }

    /**
     * Return percentage of answered questions
     * @return string
     */
    public function getCompleted()
    {
        $result = 0;
        if (!$this->getAnswers()->isEmpty()) {

            $questionCount = 0;
            foreach ($this->getSurvey()->getQuestions() as $q) {
                if ($q instanceof Question\AbstractAnswerableQuestion) {
                    $questionCount += $q->getParts()->count();
                }
            }

            if ($questionCount > 0) {
                $result = $this->getAnswers()->count() / $questionCount;
            }
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
     * @return self
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Append a comment to existing comments
     * @param string $comment
     * @return self
     */
    public function appendComment($comment)
    {
        $comment = trim($comment);
        if ($comment) {
            $comments = trim($this->getComments());
            if ($comments) {
                $comments .= PHP_EOL . PHP_EOL;
            }

            $comments = $comments . $comment;
            $this->setComments($comments);
        }

        return $this;
    }

    /**
     * Get formulas
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getQuestionnaireUsages()
    {
        return $this->questionnaireUsages;
    }

    /**
     * Notify the questionnaire that it has a new rule.
     * This should only be called by QuestionnaireUsage::setQuestionnaire()
     * @param Rule\QuestionnaireUsage $questionnaireUsage
     * @return self
     */
    public function questionnaireUsageAdded(Rule\QuestionnaireUsage $questionnaireUsage)
    {
        $this->getQuestionnaireUsages()->add($questionnaireUsage);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoleContext($action)
    {
        if ($action == 'validate') {
            return $this;
        } else {
            return $this->getSurvey();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPermissions()
    {
        $auth = \Application\Module::getServiceManager()->get('ZfcRbac\Service\AuthorizationService');

        $result = parent::getPermissions();
        foreach (array('validate') as $action) {
            $result[$action] = $auth->isActionGranted($this, $action);
        }

        return $result;
    }

    /**
     * If questionnaire change status from Validated to Complete/New, notify Questionnaire reporters that questionnaire is again editable
     * @ORM\PostPersist
     * @ORM\PostUpdate
     */
    public function notifyReporters()
    {
        if ($this->originalStatus == QuestionnaireStatus::$VALIDATED &&
                ($this->originalStatus == QuestionnaireStatus::$VALIDATED || $this->getStatus() == QuestionnaireStatus::$NEW)) {
            Utility::executeCliCommand('email notifyQuestionnaireReporters ' . $this->getId());
        }
    }

    /**
     * If questionnaire change status from New to Complete, notify validators
     * @ORM\PostPersist
     * @ORM\PostUpdate
     */
    public function notifyValidator()
    {
        if ($this->originalStatus == QuestionnaireStatus::$NEW && $this->getStatus() == QuestionnaireStatus::$COMPLETED) {
            Utility::executeCliCommand('email notifyQuestionnaireValidator ' . $this->getId());
        }
    }

    /**
     * If questionnaire change status from Complete to Validated, notify Questionnaire creator
     * @ORM\PostPersist
     * @ORM\PostUpdate
     */
    public function notifyCreator()
    {
        if ($this->originalStatus == QuestionnaireStatus::$COMPLETED &&
                $this->getStatus() == QuestionnaireStatus::$VALIDATED &&
                $this->getPermissions()['validate']
        ) {
            Utility::executeCliCommand('email notifyQuestionnaireCreator ' . $this->getId());
        }
    }

    /**
     * Get rules
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFilterQuestionnaireUsages()
    {
        return $this->filterQuestionnaireUsages;
    }

    /**
     * Notify the filter that it was added to FilterQuestionnaireUsage relation.
     * This should only be called by FilterQuestionnaireUsage::setFilter()
     * @param Rule\FilterQuestionnaireUsage $usage
     * @return self
     */
    public function filterQuestionnaireUsageAdded(Rule\FilterQuestionnaireUsage $usage)
    {
        $this->getFilterQuestionnaireUsages()->add($usage);

        return $this;
    }

    /**
     * Get populations if any specific population for this questionnaire
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getPopulations()
    {
        return $this->populations;
    }

    /**
     * Notify the questionnaire that it has a new specific population.
     * This should only be called by Population::setQuestionnaire()
     * @param Population $population
     * @return self
     */
    public function populationAdded(Population $population)
    {
        $this->getPopulations()->add($population);

        return $this;
    }

}
