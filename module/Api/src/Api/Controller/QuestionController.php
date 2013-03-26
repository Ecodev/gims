<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class QuestionController extends AbstractRestfulController
{

    protected function getJsonConfig($questionnaire = null)
    {
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
        $idQuestionnaire = $this->params('idQuestionnaire');
        $questionnaireRepository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');
        $questionnaire = $questionnaireRepository->find($idQuestionnaire);

        if (!$questionnaire) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $questions = $this->getRepository()->findBy(array(
            'survey' => $questionnaire->getSurvey(),
        ));

        return new JsonModel($this->arrayOfObjectsToArray($questions, $this->getJsonConfig($questionnaire)));
    }

}
