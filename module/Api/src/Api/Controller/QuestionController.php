<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class QuestionController extends AbstractRestfulController
{

    protected $questionnaire;

    protected function getQuestionnaire()
    {
        $idQuestionnaire = $this->params('idQuestionnaire');
        if (!$this->questionnaire && $idQuestionnaire) {
            $questionnaireRepository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');
            $this->questionnaire = $questionnaireRepository->find($idQuestionnaire);
        }

        return $this->questionnaire;
    }

    protected function getJsonConfig()
    {
        $questionnaire = $this->getQuestionnaire();

        return array(
            'name',
            'category' => array(
                'name',
                'parent' => array(
                    'name',
                    'parent' => array(
                        'name',
                    ),
                ),
            ),
            // Here we use a closure to get the questions' answers, but only for the current questionnaire
            'answers' => function(AbstractRestfulController $controller, \Application\Model\Question $question) use($questionnaire) {
                $answerRepository = $controller->getEntityManager()->getRepository('Application\Model\Answer');
                $answers = $answerRepository->findBy(array(
                    'question' => $question,
                    'questionnaire' => $questionnaire,
                ));


                return $controller->arrayOfObjectsToArray($answers, array(
                            'valuePercent',
                            'valueAbsolute',
                            'questionnaire' => array(),
                            'part' => array(
                                'name',
                            )
                ));
            }
        );
    }

    public function getList()
    {
        $questionnaire = $this->getQuestionnaire();

        // Cannot list all question, without specifying a questionnaire
        if (!$questionnaire) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $questions = $this->getRepository()->findBy(array(
            'survey' => $questionnaire->getSurvey(),
        ));

        return new JsonModel($this->arrayOfObjectsToArray($questions, $this->getJsonConfig()));
    }

}
