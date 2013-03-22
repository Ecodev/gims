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
     *   @ORM\JoinColumn(onDelete="SET NULL")
     * })
     */
    private $geoname;

    /**
     * @var Survey
     *
     * @ORM\ManyToOne(targetEntity="Survey")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable = false)
     * })
     */
    private $survey;

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
    public function setGeoname(Geoname $geoname = null)
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

}
