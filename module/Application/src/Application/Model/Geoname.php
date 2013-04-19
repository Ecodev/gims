<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Geoname
 *
 * @ORM\Entity(repositoryClass="Application\Repository\GeonameRepository")
 */
class Geoname extends AbstractModel
{

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $asciiname;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=8000, nullable=true)
     */
    private $alternatenames;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $latitude;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $longitude;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $fclass;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $fcode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=2, nullable=true)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $cc2;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $admin1;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=80, nullable=true)
     */
    private $admin2;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $admin3;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $admin4;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", nullable=true)
     */
    private $population;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $elevation;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $gtopo30;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $timezone;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     */
    private $moddate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="geometry", nullable=true)
     */
    private $geometry;

    /**
     * Set name
     *
     * @param string $name
     * @return Geoname
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
     * Set asciiname
     *
     * @param string $asciiname
     * @return Geoname
     */
    public function setAsciiname($asciiname)
    {
        $this->asciiname = $asciiname;

        return $this;
    }

    /**
     * Get asciiname
     *
     * @return string
     */
    public function getAsciiname()
    {
        return $this->asciiname;
    }

    /**
     * Set alternatenames
     *
     * @param string $alternatenames
     * @return Geoname
     */
    public function setAlternatenames($alternatenames)
    {
        $this->alternatenames = $alternatenames;

        return $this;
    }

    /**
     * Get alternatenames
     *
     * @return string
     */
    public function getAlternatenames()
    {
        return $this->alternatenames;
    }

    /**
     * Set latitude
     *
     * @param float $latitude
     * @return Geoname
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Get latitude
     *
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param float $longitude
     * @return Geoname
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Get longitude
     *
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Set fclass
     *
     * @param string $fclass
     * @return Geoname
     */
    public function setFclass($fclass)
    {
        $this->fclass = $fclass;

        return $this;
    }

    /**
     * Get fclass
     *
     * @return string
     */
    public function getFclass()
    {
        return $this->fclass;
    }

    /**
     * Set fcode
     *
     * @param string $fcode
     * @return Geoname
     */
    public function setFcode($fcode)
    {
        $this->fcode = $fcode;

        return $this;
    }

    /**
     * Get fcode
     *
     * @return string
     */
    public function getFcode()
    {
        return $this->fcode;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return Geoname
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set cc2
     *
     * @param string $cc2
     * @return Geoname
     */
    public function setCc2($cc2)
    {
        $this->cc2 = $cc2;

        return $this;
    }

    /**
     * Get cc2
     *
     * @return string
     */
    public function getCc2()
    {
        return $this->cc2;
    }

    /**
     * Set admin1
     *
     * @param string $admin1
     * @return Geoname
     */
    public function setAdmin1($admin1)
    {
        $this->admin1 = $admin1;

        return $this;
    }

    /**
     * Get admin1
     *
     * @return string
     */
    public function getAdmin1()
    {
        return $this->admin1;
    }

    /**
     * Set admin2
     *
     * @param string $admin2
     * @return Geoname
     */
    public function setAdmin2($admin2)
    {
        $this->admin2 = $admin2;

        return $this;
    }

    /**
     * Get admin2
     *
     * @return string
     */
    public function getAdmin2()
    {
        return $this->admin2;
    }

    /**
     * Set admin3
     *
     * @param string $admin3
     * @return Geoname
     */
    public function setAdmin3($admin3)
    {
        $this->admin3 = $admin3;

        return $this;
    }

    /**
     * Get admin3
     *
     * @return string
     */
    public function getAdmin3()
    {
        return $this->admin3;
    }

    /**
     * Set admin4
     *
     * @param string $admin4
     * @return Geoname
     */
    public function setAdmin4($admin4)
    {
        $this->admin4 = $admin4;

        return $this;
    }

    /**
     * Get admin4
     *
     * @return string
     */
    public function getAdmin4()
    {
        return $this->admin4;
    }

    /**
     * Set population
     *
     * @param float $population
     * @return Geoname
     */
    public function setPopulation($population)
    {
        $this->population = $population;

        return $this;
    }

    /**
     * Get population
     *
     * @return float
     */
    public function getPopulation()
    {
        return $this->population;
    }

    /**
     * Set elevation
     *
     * @param integer $elevation
     * @return Geoname
     */
    public function setElevation($elevation)
    {
        $this->elevation = $elevation;

        return $this;
    }

    /**
     * Get elevation
     *
     * @return integer
     */
    public function getElevation()
    {
        return $this->elevation;
    }

    /**
     * Set gtopo30
     *
     * @param integer $gtopo30
     * @return Geoname
     */
    public function setGtopo30($gtopo30)
    {
        $this->gtopo30 = $gtopo30;

        return $this;
    }

    /**
     * Get gtopo30
     *
     * @return integer
     */
    public function getGtopo30()
    {
        return $this->gtopo30;
    }

    /**
     * Set timezone
     *
     * @param string $timezone
     * @return Geoname
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get timezone
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set moddate
     *
     * @param \DateTime $moddate
     * @return Geoname
     */
    public function setModdate($moddate)
    {
        $this->moddate = $moddate;

        return $this;
    }

    /**
     * Get moddate
     *
     * @return \DateTime
     */
    public function getModdate()
    {
        return $this->moddate;
    }

}
