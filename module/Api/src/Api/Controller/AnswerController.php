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
     */
    protected function postCreate(AbstractModel $answer, array $data)
    {
        $answerRepository = $this->getEntityManager()->getRepository('Application\Model\Answer');
        $answerRepository->updateAbsoluteValueFromPercentageValue($answer);
    }

    /**
     * @param int   $id
     * @param array $data
     *
     * @return mixed|JsonModel
     */
    public function update($id, $data)
    {
        $result = parent::update($id, $data);

        // Compute absolute values based on percentage values
        $answerRepository = $this->getRepository();
        $answer = $answerRepository->findOneById($id);
        $answerRepository->updateAbsoluteValueFromPercentageValue($answer);

        return $result;
    }

}
