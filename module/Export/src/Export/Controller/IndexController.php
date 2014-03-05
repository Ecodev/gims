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
        $questionnaire = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findOneById($this->params('id'));
        $permission = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac')->isActionGranted($questionnaire, 'read');

        if ($permission) {
            $flatQuestions = $this->getEntityManager()->getRepository('Application\Model\Question\AbstractQuestion')->getAllWithPermissionWithAnswers('read', $questionnaire->getSurvey(), array($questionnaire->getId()));
            $questions = $this->getFlatHierarchyWithSingleRootElement($flatQuestions, 'chapter', 0);

            $view = new ExcelModel($this->params('filename'), array(
                'questions' => $questions,
                'questionnaires' => array($questionnaire)
            ));
            $view->setTemplate('export/index/export.excel.php');

            return $view;
        }
    }

    /**
     * @return ViewModel
     */
    public function surveyAction()
    {
        /* @var $survey \Application\Model\Survey */
        $survey = $this->getEntityManager()->getRepository('Application\Model\Survey')->findOneById($this->params('id'));

        // Questions permissions and organisation
        $permission = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac')->isActionGranted($survey, 'read');
        $questions = null;
        if ($permission) {

            // Questionnaires organisation
            $askedQuestionnaires = explode(',', trim($this->params()->fromQuery('questionnaires'), ','));
            $questionnaires = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->getAllWithPermission('read', null, 'survey', $survey);
            usort($questionnaires, function ($a, $b) {
                return strcmp($a->getName(), $b->getName());
            });
            $questionnaireList = array();
            if (count($askedQuestionnaires) > 1 || (count($askedQuestionnaires) == 1 && ($askedQuestionnaires[0]) > 0)) {
                foreach ($questionnaires as $questionnaire) {
                    if (in_array($questionnaire->getId(), $askedQuestionnaires)) {
                        array_push($questionnaireList, $questionnaire);
                    }
                }
            } else {
                $questionnaireList = $questionnaires;
            }

            $questionnairesIds = array_map(function($q){return $q->getId();}, $questionnaireList);
            $flatQuestions = $this->getEntityManager()->getRepository('Application\Model\Question\AbstractQuestion')->getAllWithPermissionWithAnswers('read', $survey, $questionnairesIds);
            $questions = $this->getFlatHierarchyWithSingleRootElement($flatQuestions, 'chapter', 0);

            if ($questions) {
                $view = new ExcelModel($this->params('filename'), array(
                    'questions' => $questions,
                    'questionnaires' => $questionnaireList
                ));

                $view->setTemplate('export/index/export.excel.php');

                return $view;
            }
        }
    }
}
