<?php

namespace Application\Model\Question;

use Doctrine\ORM\Mapping as ORM;

/**
 * Question whose answer is a number.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\QuestionRepository")
 */
class NumericQuestion extends AbstractPopulationQuestion
{

    /**
     * @var int
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default" = 0})
     */
    private $isAbsolute = false;

    /**
     * @return int
     */
    public function isAbsolute()
    {
        return $this->isAbsolute;
    }

    /**
     * @param int $isAbsolute
     * @return $this
     */
    public function setIsAbsolute($isAbsolute)
    {
        $this->isAbsolute = (bool) $isAbsolute;

        return $this;
    }

}
