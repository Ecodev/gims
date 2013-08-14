<?php

namespace Application\Model\Question;

use Doctrine\ORM\Mapping as ORM;

/**
 * Question whose answer is a number.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\QuestionRepository")
 */
class NumericQuestion extends AbstractAnswerableQuestion
{

}