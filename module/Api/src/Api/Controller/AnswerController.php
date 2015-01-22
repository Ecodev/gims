<?php

namespace Api\Controller;

use Application\Model\AbstractModel;
use Zend\View\Model\JsonModel;

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
        $this->getEntityManager()->refresh($answer);
        $result = $this->hydrator->extract($answer, $this->getJsonConfig());

        return new JsonModel($result);
    }

}
