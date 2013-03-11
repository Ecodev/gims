<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Survey
 *
 * @ORM\Entity(repositoryClass="Application\Repository\SurveyRepository")
 */
class Survey extends AbstractModel
{

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $code;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $active;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", nullable=true)
     */
    private $year;

    /**
     * Set name
     *
     * @param string $name
     * @return Survey
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
     * Set code
     *
     * @param string $code
     * @return Survey
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

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
     * Set active
     *
     * @param boolean $active
     * @return Survey
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set year
     *
     * @param float $year
     * @return Survey
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return float 
     */
    public function getYear()
    {
        return $this->year;
    }

}
