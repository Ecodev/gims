<?php

namespace Application\Repository;

class ChoiceRepository extends AbstractRepository
{

    public function updateAnswersPercentValue(\Application\Model\Question\Choice $choice)
    {
        /** @var \Application\Repository\AnswerRepository $answerRepository */
        $answerRepository = $this->getEntityManager()->getRepository('\Application\Model\Answer');

        /** @var \Application\Model\Answer $answer */
        foreach ($choice->getAnswers() as $answer) {
            $answerRepository->updatePercentValueFromChoiceValue($answer);
        }
    }
}
