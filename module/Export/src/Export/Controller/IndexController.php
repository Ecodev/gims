<?php

namespace Export\Controller;

use Application\View\Model\ExcelModel;
use Application\Traits\FlatHierarchic;

class IndexController extends \Application\Controller\AbstractAngularActionController
{

    use FlatHierarchic;

    /**
     * @return ViewModel
     */
    public function questionnaireAction()
    {
        $hydrator = new \Application\Service\Hydrator();

        $questionnaire = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findOneById($this->params('id'));

        $permission = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac')->isActionGranted($questionnaire, 'read');
        if ($permission) {
            $questions = $this->getEntityManager()->getRepository('Application\Model\Question\AbstractQuestion')->getAllWithPermission('read', null, 'survey', $questionnaire->getSurvey());
        }

        // prepare flat array of questions for then be reordered by Parent > childrens > childrens
        $flatQuestions = array();
        foreach ($questions as $question) {
            $flatQuestion = $hydrator->extract($question, array('type', 'filter', 'answers', 'answers.part', 'answers.questionnaire', 'choices', 'parts', 'isMultiple', 'chapter'));
            array_push($flatQuestions, $flatQuestion);
        }

        $questions = $this->getFlatHierarchyWithSingleRootElement($flatQuestions, 'chapter' ,0);


        $view = new ExcelModel($this->params('filename'), array('questions' => $questions, 'questionnaires' => array($questionnaire)));
        $view->setTemplate('export/index/export.excel.php');
        return $view;
    }

    /**
     * @return ViewModel
     */
    public function surveyAction()
    {
        $hydrator = new \Application\Service\Hydrator();

        /* @var $survey \Application\Model\Survey */
        $survey = $this->getEntityManager()->getRepository('Application\Model\Survey')->findOneById($this->params('id'));

        // Questions permissions and organisation
        $permission = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac')->isActionGranted($survey, 'read');
        $questions = null;
        if ($permission) {
            $questions = $this->getEntityManager()->getRepository('Application\Model\Question\AbstractQuestion')->getAllWithPermission('read', null, 'survey', $survey);

            // prepare flat array of questions for then be reordered by Parent > childrens > childrens
            $flatQuestions = array();
            foreach ($questions as $question) {
                $flatQuestion = $hydrator->extract($question, array('type', 'filter', 'answers', 'answers.part', 'answers.questionnaire', 'choices', 'parts', 'isMultiple', 'chapter'));
                array_push($flatQuestions, $flatQuestion);
            }

            $questions = $this->getFlatHierarchyWithSingleRootElement($flatQuestions, 'chapter' ,0);
        }

        if ($questions) {

            // Questionnaires organisation
            $askedQuestionnaires = explode(',', trim($this->params()->fromQuery('questionnaires'), ','));
            $questionnaires = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->getAllWithPermission('read', null, 'survey', $survey);
            usort($questionnaires, function($a,$b){return strcmp($a->getName(), $b->getName());});
            $questionnaireList = array();
            if (count($askedQuestionnaires) > 1 || (count($askedQuestionnaires) == 1 && ($askedQuestionnaires[0]) > 0) ) {
                foreach ($questionnaires as $questionnaire) {

                    if (in_array($questionnaire->getId(), $askedQuestionnaires)) {
                        array_push($questionnaireList, $questionnaire);
                    }
                }
            } else {
                $questionnaireList = $questionnaires;
            }

            $view = new ExcelModel($this->params('filename'), array('questions' => $questions, 'questionnaires' => $questionnaireList));
            $view->setTemplate('export/index/export.excel.php');
            return $view;
        }
    }
}
