<?php

namespace Api\Controller;

use Application\Model\Questionnaire;
use Application\Model\Survey;
use Zend\View\Model\JsonModel;

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

        // Permission is not handled for now
        #$config['permission'] = function (\Application\Service\Hydrator $hydrator, Questionnaire $questionnaire) use ($controller) {
        #    $questionnaireAssertion = new QuestionnaireAssertion($questionnaire);
        #    /* @var $rbac \Application\Service\Rbac */
        #    $rbac = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac');
        #    $questionnaireAssertion->setRbac($rbac); // @todo temporary code waiting questionnaire assertion to be able to get rbac service
        #    return array(
        #        'canBeCompleted' => true, //$questionnaireAssertion->canBeCompleted(),
        #        'canBeValidated ' => $questionnaireAssertion->canBeValidated(),
        #        'canBeDeleted' => $questionnaireAssertion->canBeDeleted(),
        #        'canBeUpdated' => true, // @todo implement me
        #        'isLocked' => false, // @todo implement me
        #    );
        #};

        return $config;
    }

    public function getList()
    {
        $survey = $this->getParent();
        $q = $this->params()->fromQuery('q');
        $questionnaires = $this->getRepository()->getAll($survey, $q);

        return new JsonModel($this->hydrator->extractArray($questionnaires, $this->getJsonConfig()));
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
        if (isset($data['status']) &&
                $data['status'] != $questionnaire->getStatus() &&
                ($data['status'] == \Application\Model\QuestionnaireStatus::$VALIDATED ||
                $questionnaire->getStatus() == \Application\Model\QuestionnaireStatus::$VALIDATED)) {
            if (!$this->getRbac()->isActionGranted($questionnaire, 'validate')) {
                $this->getResponse()->setStatusCode(401);

                return new JsonModel(array('message' => $this->getRbac()->getMessage()));
            }
        }

        return parent::update($id, $data);
    }

}
