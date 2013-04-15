<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Questionnaire
 *
 * @ORM\Entity(repositoryClass="Application\Repository\QuestionnaireRepository")
 */
class Questionnaire extends AbstractModel implements \Application\Service\RoleContextInterface
{

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
     * @ORM\ManyToOne(targetEntity="Survey")
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
    public function setDateObservationStart($dateObservationStart)
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
    public function setDateObservationEnd($dateObservationEnd)
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
     * Returns the computed value of the given category, based on the questionnaire's available answers
     * @param \Application\Model\Category $category
     * @param \Application\Model\Part $part
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadySummedCategories this should be null for first call, recursive calls will used to avoid duplicates
     * @return float|null null if no answer at all, otherwise the value
     */
    public function compute(Category $category, Part $part = null, \Doctrine\Common\Collections\ArrayCollection $alreadySummedCategories = null)
    {
        if (!$alreadySummedCategories) {
            $alreadySummedCategories = new \Doctrine\Common\Collections\ArrayCollection();
        }

        // Avoid duplicates
        if ($alreadySummedCategories->contains($category)) {
            return null;
        } else {
            $alreadySummedCategories->add($category);
        }

        // If the category have a specified answer, returns it (skip all computation)
        foreach ($this->getAnswers() as $answer) {
            $answerCategory = $answer->getQuestion()->getCategory()->getOfficialCategory() ? : $answer->getQuestion()->getCategory();
            if ($answerCategory == $category && $answer->getPart() == $part) {

                $alreadySummedCategories->add(true);
                return $answer->getValueAbsolute();
            }
        }


        // Summer to sum values of given categories, but only if non-null (to preserve null value if no answer at all)
        $summer = function(\IteratorAggregate $categories) use ($part, $alreadySummedCategories) {
                    $sum = null;
                    foreach ($categories as $c) {
                        $summandValue = $this->compute($c, $part, $alreadySummedCategories);
                        if (!is_null($summandValue)) {
                            $sum += $summandValue;
                        }
                    }

                    return $sum;
                };

        // First, attempt to sum summands
        $sum = $summer($category->getSummands());

        // If no sum so far, we use children instead. This is "normal case"
        if (is_null($sum)) {
            $sum = $summer($category->getChildren());
        }

        return $sum;
    }

}

