<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class AnswerController extends AbstractRestfulController
{

    /**
     * @return mixed|JsonModel
     */
    public function getList()
    {
        $idQuestionnaire = $this->params('idQuestionnaire');
        $c = array(
            'questionnaire' => $idQuestionnaire,
        );

        $objects = $this->getRepository()->findBy($c);

        return new JsonModel($this->hydrator->extractArray($objects, $this->getJsonConfig()));
    }

    /**
     * @param array    $data
     *
     * @param callable $postAction
     *
     * @throws \Exception
     * @return mixed|void|JsonModel
     */
    public function create($data, \Closure $postAction = null)
    {
        $result = parent::create($data, function(\Application\Model\Answer $answer) {

                            // Compute absolute values based on percentage values
                            $answerRepository = $this->getEntityManager()->getRepository('Application\Model\Answer');
                            $answerRepository->updateAbsoluteValueFromPercentageValue($answer);
                        });


        return $result;
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
