<?php

namespace Application\Model\Rule;

use Doctrine\ORM\Mapping as ORM;
use Application\Model\Filter;

/**
 * Ratio is a way to define a filter as a ratio of another one.
 *
 * @ORM\Entity
 */
class Ratio extends AbstractRule
{

    public function getName()
    {
        return 'Ratio from another filter';
    }

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=4, scale=3, nullable=true)
     */
    private $ratio;

    /**
     * @var Filter
     *
     * @ORM\ManyToOne(targetEntity="Application\Model\Filter", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(onDelete="CASCADE")
     * })
     */
    private $filter;

    /**
     * Set ratio (between 0.0 and 1.0)
     *
     * @param float $ratio
     * @return Answer
     */
    public function setRatio($ratio)
    {
        $this->ratio = $ratio;

        return $this;
    }

    /**
     * Get ratio (between 0.0 and 1.0)
     *
     * @return float
     */
    public function getRatio()
    {
        return (float) $this->ratio;
    }

    /**
     * Set filter
     *
     * @param Filter $filter
     * @return Question
     */
    public function setFilter(Filter $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Get filter
     *
     * @return Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

}
