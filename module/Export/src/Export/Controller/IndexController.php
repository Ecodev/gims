<?php

namespace Export\Controller;

use Application\View\Model\ExcelModel;
use Application\Traits\FlatHierarchicQuestions;

class IndexController extends \Application\Controller\AbstractAngularActionController
{

    use FlatHierarchicQuestions;

    /**
     * @return ViewModel
     */
    public function questionnaireAction()
    {
        $questionnaire = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findOneById($this->params('id'));

        $permission = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac')->isActionGranted($questionnaire, 'read');
        if ($permission) {
            $questions = $this->getEntityManager()
                              ->getRepository('Application\Model\Question\AbstractQuestion')
                              ->getAllWithPermission('read', 'survey', $questionnaire->getSurvey());
        }

        $questions = $this->getFlatHierarchy(
            $questions,
            array(
                'type',
                'filter',
                'chapter',
                'answers',
                'answers.part',
                'answers.questionnaire',
                'choices',
                'parts',
                'isMultiple',
                'chapter'),
            new \Application\Service\Hydrator()
        );

        return new ExcelModel($this->params('filename'), array('questions' => $questions, 'questionnaire' => $questionnaire));
    }

}
