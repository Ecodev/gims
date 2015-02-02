<?php

namespace Application\Model\Question;

use Doctrine\ORM\Mapping as ORM;

/**
 * Question whose answer is a text.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\QuestionRepository")
 */
class TextQuestion extends AbstractAnswerableQuestion
{
}
