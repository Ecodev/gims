<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Export\Controller;

use Zend\View\Model\ViewModel;
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
            $questions = $this->getEntityManager()->getRepository('Application\Model\Question\AbstractQuestion')->getAllWithPermission('read', 'survey', $questionnaire->getSurvey());
        }
        $questions = $this->getFlatHierarchy($questions, array('type','filter','chapter','answers','answers.part','choices','parts','isMultiple','chapter'), new \Application\Service\Hydrator());

        return new ViewModel(array('questions' => $questions));
    }

}
