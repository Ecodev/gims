<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;
use Application\Model\AbstractModel;

class AnswerController extends AbstractChildRestfulController
{

    /**
     * Compute absolute values based on percentage values of newly created answer
     * @param \Application\Model\AbstractModel $answer
     * @param array $data
     * @return void|\Zend\View\Model\JsonModel
     */
    protected function postCreate(AbstractModel $answer, array $data)
    {
        return $this->completePopulationAnswer($answer, $data);
    }

    /**
     * Compute absolute values based on percentage values of newly created answer
     * @param \Application\Model\AbstractModel $answer
     * @param array $data
     * @return void|\Zend\View\Model\JsonModel
     */
    protected function postUpdate(AbstractModel $answer, array $data)
    {
        return $this->completePopulationAnswer($answer, $data);
    }

    protected function completePopulationAnswer(AbstractModel $answer)
    {
        $answerRepository = $this->getEntityManager()->getRepository('Application\Model\Answer');
        $answerRepository->completePopulationAnswer($answer);
        $this->getEntityManager()->clear('Application\Model\Answer'); // clear answers to recover new values
        $answer = $answerRepository->findOneById($answer->getId());

        $result = $this->hydrator->extract($answer, $this->getJsonConfig());

        return new JsonModel($result);
    }

}
