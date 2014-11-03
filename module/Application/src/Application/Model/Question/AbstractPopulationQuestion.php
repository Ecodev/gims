<?php

namespace Application\Model\Question;

use Doctrine\ORM\Mapping as ORM;

/**
 * A question which can represent a population, and thus have automatic percentage to absolute computation.
 *
 * The end-user enter percentage values, which will be converted to absolute internally based on population data.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\QuestionRepository")
 */
abstract class AbstractPopulationQuestion extends AbstractAnswerableQuestion
{

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default" = 0})
     */
    private $isPopulation = false;

    /**
     * Returns whether this question must be answered
     * @return boolean
     */
    public function isPopulation()
    {
        return $this->isPopulation;
    }

    /**
     * @param boolean $isPopulation
     * @return self
     */
    public function setIsPopulation($isPopulation)
    {
        $this->isPopulation = (bool) $isPopulation;

        return $this;
    }

}
