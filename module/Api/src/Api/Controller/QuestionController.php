<?php

namespace Api\Controller;

use Application\Module;
use Application\Model\Question;
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
            'filter' => array(
                'name',
                'parents' => array(
                    'name',
                    'parents' => array(
                        'name',
                    ),
                ),
            ),
            // Here we use a closure to get the questions' answers, but only for the current questionnaire
            'answers' => function(AbstractRestfulController $controller, Question $question) use($questionnaire) {
                $answerRepository = $controller->getEntityManager()->getRepository('Application\Model\Answer');
                $answers = $answerRepository->findBy(array(
                    'question' => $question,
                    'questionnaire' => $questionnaire,
                ));

                $answers = $controller->arrayOfObjectsToArray($answers, array(
                            'valuePercent',
                            'valueAbsolute',
                            'questionnaire' => array(),
                            'part' => array(
                                'name',
                            )
                ));

                // special case for question, reorganize keys for the needs of NgGrid:
                // Numerical key must correspond to the id of the part.
                $output = array();
                foreach ($answers as $answer) {

                    if (! empty($answer['part']['id'])) {
                        $output[$answer['part']['id']] = $answer;
                    } else {
                        // It is ok to have one answer in position 0 (= total) but not more!
                        // should not be the case... so log it
                        if (! empty($output[0])) {
                            $logger = Module::getServiceManager()->get('Zend\Log');
                            $logger->info(sprintf('[WARNING] Answer object "%s" has too many null Part. ', $answer['id']));
                        }
                        $output[0] = $answer;
                    }
                }
                return $output;
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

//    public function update($id, $data)
//    {
////        $rbac->isGrantedWithContext($questionnaire, 'permission name specific to the questionnaire');
//    }

}
