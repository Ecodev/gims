<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Population data for each country-year-part triple.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\PopulationRepository")
 * @ORM\Table(uniqueConstraints={
 *     @ORM\UniqueConstraint(name="population_unique_official",columns={"year", "country_id", "part_id"}, where="questionnaire_id IS NULL"),
 *     @ORM\UniqueConstraint(name="population_unique_non_official",columns={"year", "country_id", "part_id", "questionnaire_id"}, where="questionnaire_id IS NOT NULL")
 * })
 */
class Population extends AbstractModel
{

    /**
     * The year
     * @var integer
     *
     * @ORM\Column(type="decimal", precision=4, scale=0)
     */
    private $year;

    /**
     * The country
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $country;

    /**
     * The absolute number of people
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $population;

    /**
     * The part of the country
     * @var Part
     *
     * @ORM\ManyToOne(targetEntity="Part")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $part;

    /**
     * Optionnal questionnaire.
     * If exists, means that the population is only used for that Questionnaire.
     * @var Questionnaire
     *
     * @ORM\ManyToOne(targetEntity="Questionnaire", inversedBy="populations")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $questionnaire;

    /**
     * {@inheritDoc}
     */
    public function getJsonConfig()
    {
        return array_merge(parent::getJsonConfig(), array(
            'year',
            'population',
        ));
    }

    /**
     * Set year
     *
     * @param integer $year
     * @return self
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
        return (int) $this->year;
    }

    /**
     * Set country
     *
     * @param Country $country
     * @return self
     */
    public function setCountry(Country $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set population
     *
     * @param integer $population
     * @return self
     */
    public function setPopulation($population)
    {
        $this->population = $population;

        return $this;
    }

    /**
     * Get population
     *
     * @return integer
     */
    public function getPopulation()
    {
        return $this->population;
    }

    /**
     * Set part
     *
     * @param Part $part
     * @return self
     */
    public function setPart(Part $part)
    {
        $this->part = $part;

        return $this;
    }

    /**
     * Get part
     *
     * @return Part
     */
    public function getPart()
    {
        return $this->part;
    }

    /**
     * Set questionnaire
     *
     * @param Questionnaire $questionnaire
     * @return self
     */
    public function setQuestionnaire(Questionnaire $questionnaire = null)
    {
        $this->questionnaire = $questionnaire;
        $this->questionnaire->populationAdded($this);

        return $this;
    }

    /**
     * Get optionnal questionnaire.
     * If exists, means that the population is only used for that Questionnaire.
     *
     * @return Questionnaire|null
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoleContext($action)
    {
        return $this->getQuestionnaire() ? : new \Application\Service\MissingRequiredRoleContext('questionnaire');
    }

}
