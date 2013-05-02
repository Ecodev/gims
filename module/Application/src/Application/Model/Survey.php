<?php

namespace Application\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Survey is a campaign to gather data for a specific year, so it's a group of questions.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\SurveyRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="survey_code_unique",columns={"code"})})
 */
class Survey extends AbstractModel implements \Application\Service\RoleContextInterface
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
    private $active = false;

    /**
     * @var integer
     *
     * @ORM\Column(type="decimal", precision=4, scale=0, nullable=true)
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
     * @param integer $year
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
     * @return integer
     */
    public function getYear()
    {
        return (int)$this->year;
    }

}
