<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Population data for each country-year-part triple. Imported from http://esa.un.org/unpd/wup/
 *
 * @ORM\Entity(repositoryClass="Application\Repository\PopulationRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="population_unique",columns={"year", "country_id", "part_id"})})
 */
class Population extends AbstractModel
{

    /**
     * @var array
     */
    protected static $jsonConfig
        = array();

    /**
     * @var integer
     *
     * @ORM\Column(type="decimal", precision=4, scale=0)
     */
    private $year;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * })
     */
    private $country;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $population;

    /**
     * @var Part
     *
     * @ORM\ManyToOne(targetEntity="Part")
     */
    private $part;

    /**
     * Set year
     *
     * @param integer $year
     * @return Population
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
        return (int)$this->year;
    }

    /**
     * Set country
     *
     * @param Country $country
     * @return Population
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
     * @return Population
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
     * @return Answer
     */
    public function setPart(Part $part = null)
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

}
