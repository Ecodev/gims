<?php

namespace Application\Model\Question;

use Doctrine\ORM\Mapping as ORM;

/**
 * A question which can be answered by end-user, and thus may be specific to parts.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\QuestionRepository")
 */
abstract class AbstractPopulationQuestion extends AbstractAnswerableQuestion
{

    /**
     * @var int
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
     * @return $this
     */
    public function setIsPopulation($isPopulation)
    {
        $this->isPopulation = (bool) $isPopulation;

        return $this;
    }

}
