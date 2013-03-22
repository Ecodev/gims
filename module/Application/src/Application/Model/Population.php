<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Population
 *
 * @ORM\Entity(repositoryClass="Application\Repository\PopulationRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="population_unique",columns={"year", "country_id"})})
 */
class Population extends AbstractModel
{

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
     *   @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $country;
    
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $urban;
    
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $rural;
    
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     */
    private $total;

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
        return $this->year;
    }

    /**
     * Set country
     *
     * @param Country $country
     * @return Population
     */
    public function setCountry(Country $country = null)
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
     * Set urban
     *
     * @param integer $urban
     * @return Population
     */
    public function setUrban($urban)
    {
        $this->urban = $urban;

        return $this;
    }

    /**
     * Get urban
     *
     * @return integer
     */
    public function getUrban()
    {
        return $this->urban;
    }

    /**
     * Set rural
     *
     * @param integer $rural
     * @return Population
     */
    public function setRural($rural)
    {
        $this->rural = $rural;

        return $this;
    }

    /**
     * Get rural
     *
     * @return integer
     */
    public function getRural()
    {
        return $this->rural;
    }

    /**
     * Set total
     *
     * @param integer $total
     * @return Population
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return integer
     */
    public function getTotal()
    {
        return $this->total;
    }

}
