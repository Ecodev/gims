<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Country. Data are imported from http://www.geonames.org
 *
 * @ORM\Entity(repositoryClass="Application\Repository\CountryRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="country_code_unique",columns={"code"})})
 */
class Country extends AbstractModel
{

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $iso3;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $isoNumeric;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $fips;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $capital;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    private $area;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $continent;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $tld;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $currencyCode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $currencyName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=60, nullable=true)
     */
    private $postalCodeFormat;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $postalCodeRegexp;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $languages;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=75, nullable=true)
     */
    private $neighbors;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $equivalentFipsCode;

    /**
     * @var Geoname
     *
     * @ORM\ManyToOne(targetEntity="Geoname")
     */
    private $geoname;

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set iso3
     *
     * @param string $iso3
     * @return Country
     */
    public function setIso3($iso3)
    {
        $this->iso3 = $iso3;

        return $this;
    }

    /**
     * Get iso3
     *
     * @return string
     */
    public function getIso3()
    {
        return $this->iso3;
    }

    /**
     * Set isoNumeric
     *
     * @param integer $isoNumeric
     * @return Country
     */
    public function setIsoNumeric($isoNumeric)
    {
        $this->isoNumeric = $isoNumeric;

        return $this;
    }

    /**
     * Get isoNumeric
     *
     * @return integer
     */
    public function getIsoNumeric()
    {
        return $this->isoNumeric;
    }

    /**
     * Set fips
     *
     * @param string $fips
     * @return Country
     */
    public function setFips($fips)
    {
        $this->fips = $fips;

        return $this;
    }

    /**
     * Get fips
     *
     * @return string
     */
    public function getFips()
    {
        return $this->fips;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Country
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
     * Set capital
     *
     * @param string $capital
     * @return Country
     */
    public function setCapital($capital)
    {
        $this->capital = $capital;

        return $this;
    }

    /**
     * Get capital
     *
     * @return string
     */
    public function getCapital()
    {
        return $this->capital;
    }

    /**
     * Set area
     *
     * @param float $area
     * @return Country
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return float
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set continent
     *
     * @param string $continent
     * @return Country
     */
    public function setContinent($continent)
    {
        $this->continent = $continent;

        return $this;
    }

    /**
     * Get continent
     *
     * @return string
     */
    public function getContinent()
    {
        return $this->continent;
    }

    /**
     * Set tld
     *
     * @param string $tld
     * @return Country
     */
    public function setTld($tld)
    {
        $this->tld = $tld;

        return $this;
    }

    /**
     * Get tld
     *
     * @return string
     */
    public function getTld()
    {
        return $this->tld;
    }

    /**
     * Set currencyCode
     *
     * @param string $currencyCode
     * @return Country
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    /**
     * Get currencyCode
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * Set currencyName
     *
     * @param string $currencyName
     * @return Country
     */
    public function setCurrencyName($currencyName)
    {
        $this->currencyName = $currencyName;

        return $this;
    }

    /**
     * Get currencyName
     *
     * @return string
     */
    public function getCurrencyName()
    {
        return $this->currencyName;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Country
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set postalCodeFormat
     *
     * @param string $postalCodeFormat
     * @return Country
     */
    public function setPostalCodeFormat($postalCodeFormat)
    {
        $this->postalCodeFormat = $postalCodeFormat;

        return $this;
    }

    /**
     * Get postalCodeFormat
     *
     * @return string
     */
    public function getPostalCodeFormat()
    {
        return $this->postalCodeFormat;
    }

    /**
     * Set postalCodeRegexp
     *
     * @param string $postalCodeRegexp
     * @return Country
     */
    public function setPostalCodeRegexp($postalCodeRegexp)
    {
        $this->postalCodeRegexp = $postalCodeRegexp;

        return $this;
    }

    /**
     * Get postalCodeRegexp
     *
     * @return string
     */
    public function getPostalCodeRegexp()
    {
        return $this->postalCodeRegexp;
    }

    /**
     * Set languages
     *
     * @param string $languages
     * @return Country
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;

        return $this;
    }

    /**
     * Get languages
     *
     * @return string
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * Set neighbors
     *
     * @param string $neighbors
     * @return Country
     */
    public function setNeighbors($neighbors)
    {
        $this->neighbors = $neighbors;

        return $this;
    }

    /**
     * Get neighbors
     *
     * @return string
     */
    public function getNeighbors()
    {
        return $this->neighbors;
    }

    /**
     * Set equivalentFipsCode
     *
     * @param string $equivalentFipsCode
     * @return Country
     */
    public function setEquivalentFipsCode($equivalentFipsCode)
    {
        $this->equivalentFipsCode = $equivalentFipsCode;

        return $this;
    }

    /**
     * Get equivalentFipsCode
     *
     * @return string
     */
    public function getEquivalentFipsCode()
    {
        return $this->equivalentFipsCode;
    }

    /**
     * Set geoname
     *
     * @param Geoname $geoname
     * @return Country
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

}
