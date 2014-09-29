<?php

namespace Api\Controller;

use Application\Model\Questionnaire;
use Application\Model\Survey;
use \Application\Model\QuestionnaireStatus;
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
            $role = $roleRepository->findOneByName('Questionnaire reporter');

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
            $role = $roleRepository->findOneByName('Questionnaire validator');

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
        $parent = $this->getParent();
        $q = $this->params()->fromQuery('q');
        $surveyTypes = $this->getSurveyTypes();

        $questionnaires = $this->getRepository()->getAllWithPermission($this->params()->fromQuery('permission', 'read'), $q, $this->params('parent'), $parent, $surveyTypes);
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
        $oldStatus = $questionnaire->getStatus();
        $newStatus = isset($data['status']) ? $data['status'] : null;
        if (!is_null($newStatus) && $oldStatus != $newStatus) {

            if ($oldStatus == QuestionnaireStatus::$PUBLISHED || $newStatus == QuestionnaireStatus::$PUBLISHED) {
                $this->checkActionGranted($questionnaire, 'publish');
            }

            if ($oldStatus == QuestionnaireStatus::$VALIDATED || $newStatus == QuestionnaireStatus::$VALIDATED) {
                $this->checkActionGranted($questionnaire, 'validate');
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
        $role = $this->getEntityManager()->getRepository('Application\Model\Role')->findOneByName('Questionnaire reporter');
        $userQuestionnaire = new \Application\Model\UserQuestionnaire();
        $userQuestionnaire->setUser($user)->setQuestionnaire($questionnaire)->setRole($role);

        $this->getEntityManager()->persist($userQuestionnaire);
        $this->getEntityManager()->flush();
    }

}
