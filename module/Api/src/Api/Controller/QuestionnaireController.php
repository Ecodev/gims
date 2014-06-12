<?php

namespace Api\Controller;

use Application\Model\Questionnaire;
use Application\Model\Survey;
use Zend\View\Model\JsonModel;
use Application\Model\AbstractModel;

class QuestionnaireController extends AbstractChildRestfulController
{

    /**
     * @var Survey
     */
    protected $survey;

    protected function getClosures()
    {
        $controller = $this;

        $config = array();
        $config['dateLastAnswerModification'] = function (
                \Application\Service\Hydrator $hydrator,
                Questionnaire $questionnaire
                ) use ($controller) {
            $result = null;

            $answerRepository = $controller->getEntityManager()->getRepository('Application\Model\Answer');
            $criteria = array(
                'questionnaire' => $questionnaire->getId(),
            );
            $order = array(
                'dateModified' => 'DESC',
            );
            /** @var \Application\Model\Answer $answer */
            $answer = $answerRepository->findOneBy($criteria, $order);
            if ($answer) {
                $result = $answer->getDateModified() === null ?
                        $answer->getDateCreated()->format(DATE_ISO8601) :
                        $answer->getDateModified()->format(DATE_ISO8601);
            }

            return $result;
        };

        $config['reporterNames'] = function (
                \Application\Service\Hydrator $hydrator, Questionnaire $questionnaire
                ) use ($controller) {
            $roleRepository = $controller->getEntityManager()->getRepository('Application\Model\Role');

            // @todo find a way making sure we have a role reporter
            /** @var \Application\Model\Role $role */
            $role = $roleRepository->findOneByName('reporter');

            $userQuestionnaireRepository = $controller->getEntityManager()->getRepository(
                    'Application\Model\UserQuestionnaire'
            );
            $criteria = array(
                'questionnaire' => $questionnaire,
                'role' => $role,
            );

            $results = array();

            /** @var \Application\Model\UserQuestionnaire $userQuestionnaire */
            foreach ($userQuestionnaireRepository->findBy($criteria) as $userQuestionnaire) {
                $results[] = $userQuestionnaire->getUser()->getName();
            }

            return implode(',', $results);
        };

        $config['validatorNames'] = function (
                \Application\Service\Hydrator $hydrator, Questionnaire $questionnaire
                ) use ($controller) {
            $roleRepository = $controller->getEntityManager()->getRepository('Application\Model\Role');

            // @todo find a way making sure we have a role reporter
            /** @var \Application\Model\Role $role */
            $role = $roleRepository->findOneByName('validator');

            $userQuestionnaireRepository = $controller->getEntityManager()->getRepository('Application\Model\UserQuestionnaire');
            $criteria = array(
                'questionnaire' => $questionnaire,
                'role' => $role,
            );

            $results = array();
            /** @var \Application\Model\UserQuestionnaire $userQuestionnaire */
            foreach ($userQuestionnaireRepository->findBy($criteria) as $userQuestionnaire) {
                $results[] = $userQuestionnaire->getUser()->getName();
            }

            return implode(',', $results);
        };

        return $config;
    }

    public function getList()
    {
        $survey = $this->getParent();
        $q = $this->params()->fromQuery('q');
        $questionnaires = $this->getRepository()->getAllWithPermission($this->params()->fromQuery('permission', 'read'), $this->params()->fromQuery('q'), 'survey', $survey, $q);
        $jsonData = $this->paginate($questionnaires);

        return new JsonModel($jsonData);
    }

    /**
     * @param int   $id
     * @param array $data
     *
     * @return mixed|JsonModel
     */
    public function update($id, $data)
    {
        /** @var $questionnaire \Application\Model\Questionnaire */
        $questionnaire = $this->getRepository()->findOneById($id);

        // If trying to validate, or un-validate, a questionnaire, we must check the permission to do that
        if (isset($data['status']) && $data['status'] != $questionnaire->getStatus()) {
            if (($data['status'] == \Application\Model\QuestionnaireStatus::$VALIDATED ||
                    $questionnaire->getStatus() == \Application\Model\QuestionnaireStatus::$VALIDATED) &&
                    !$this->getAuth()->isActionGranted($questionnaire, 'validate')) {
                $this->getResponse()->setStatusCode(401);

                return new JsonModel(array('message' => $this->getAuth()->getMessage()));
            } elseif (($data['status'] == \Application\Model\QuestionnaireStatus::$PUBLISHED ||
                    $questionnaire->getStatus() == \Application\Model\QuestionnaireStatus::$PUBLISHED) &&
                    !$this->getAuth()->isActionGranted($questionnaire, 'publish')) {
                $this->getResponse()->setStatusCode(401);

                return new JsonModel(array('message' => $this->getAuth()->getMessage()));
            }
        }

        return parent::update($id, $data);
    }

    /**
     * Give reporter role to the user on the new questionnaire, so he can answer questions
     * @param \Application\Model\AbstractModel $questionnaire
     */
    protected function postCreate(AbstractModel $questionnaire, array $data)
    {
        $user = $this->getAuth()->getIdentity();
        $role = $this->getEntityManager()->getRepository('Application\Model\Role')->findOneByName('reporter');
        $userQuestionnaire = new \Application\Model\UserQuestionnaire();
        $userQuestionnaire->setUser($user)->setQuestionnaire($questionnaire)->setRole($role);

        $this->getEntityManager()->persist($userQuestionnaire);
        $this->getEntityManager()->flush();
    }

    public function copyFilterUsagesAction()
    {
        $destQ = $this->getRepository()->findOneById($this->params()->fromQuery('dest'));
        $srcQ = $this->getRepository()->findOneById($this->params()->fromQuery('src'));

        $this->getRepository()->copyFilterUsages($destQ, $srcQ);

        return new \Zend\View\Model\JsonModel(array($destQ->getFilterQuestionnaireUsages()->count()));
    }

}
