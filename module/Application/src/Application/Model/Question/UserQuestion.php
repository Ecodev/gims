<?php

namespace Application\Model\Question;

use Doctrine\ORM\Mapping as ORM;

/**
 * Question whose answer is a user existing in GIMS database.
 *
 * @ORM\Entity(repositoryClass="Application\Repository\QuestionRepository")
 */
class UserQuestion extends AbstractAnswerableQuestion
{
}
