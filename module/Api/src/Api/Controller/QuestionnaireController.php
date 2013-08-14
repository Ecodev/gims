<?php

namespace Api\Controller;

use Application\Assertion\QuestionnaireAssertion;
use Application\Model\Questionnaire;
use Application\Model\Survey;
use Zend\View\Model\JsonModel;

class QuestionnaireController extends AbstractRestfulController
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
        $survey = $this->getSurvey();
        $q = $this->params()->fromQuery('q');
        $questionnaires = $this->getRepository()->getAll($survey, $q);

        return new JsonModel($this->hydrator->extractArray($questionnaires, $this->getJsonConfig()));
    }

    /**
     * @param int $id
     *
     * @return mixed|JsonModel
     */
    public function delete($id)
    {
        $questionnaire = $this->getRepository()->findOneBy(array('id' => $id));

        if (!$questionnaire) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $questionnaireAssertion = new QuestionnaireAssertion($questionnaire);

        /* @var $rbac \Application\Service\Rbac */
        $rbac = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac');
        $questionnaireAssertion->setRbac($rbac);

        if (!$questionnaireAssertion->canBeDeleted()) {
            $this->getResponse()->setStatusCode(403);
            return new JsonModel(array('message' => 'Forbidden action'));
        }

        return parent::delete($id);
    }

    /**
     * @param int   $id
     * @param array $data
     *
     * @return mixed|JsonModel
     */
    public function update($id, $data)
    {
        // Retrieve question since permissions apply against it.
        /** @var $questionnaire \Application\Model\Questionnaire */
        $questionnaireRepository = $this->getEntityManager()->getRepository($this->getModel());
        $questionnaire = $questionnaireRepository->findOneById($id);
        $survey = $questionnaire->getSurvey();

        // @todo check here or in assertion if status has changed
        // Update object or not...
        if ($this->isAllowedSurvey($survey) && $this->isAllowedQuestionnaire($questionnaire)) {
            $result = parent::update($id, $data);
        } else {
            $this->getResponse()->setStatusCode(401);
            $result = new JsonModel(array('message' => 'Authorization required'));
        }
        return $result;
    }

    /**
     * @param array    $data
     *
     * @param callable $postAction
     *
     * @throws \Exception
     * @return mixed|void|JsonModel
     */
    public function create($data, \Closure $postAction=null)
    {
        // Check that all required properties are given by the GUI
        $properties = $this->getMetaModelService()->getMandatoryProperties();
        $dataKeys = array_keys($data);

        foreach ($properties as $propertyName) {
            if (!in_array($propertyName, $dataKeys)) {
                throw new \Exception('Missing property ' . $propertyName, 1368459231);
            }
        }

        // Retrieve a questionnaire from the storage
        $repository = $this->getEntityManager()->getRepository('Application\Model\Survey');
        $survey = $repository->findOneById($data['survey']);

        // Update object or not...
        if ($this->isAllowedSurvey($survey)) {
            $result = parent::create($data);
        } else {
            // @todo code should be 403 if not enough permission
            $this->getResponse()->setStatusCode(401);
            $result = new JsonModel(array('message' => 'Authorization required'));
        }
        return $result;
    }

    /**
     * @return Survey|null
     */
    protected function getSurvey()
    {
        $idSurvey = $this->params('idParent');
        if (!$this->survey && $idSurvey) {
            $surveyRepository = $this->getEntityManager()->getRepository('Application\Model\Survey');
            $this->survey = $surveyRepository->find($idSurvey);
        }

        return $this->survey;
    }

    /**
     * Ask Rbac whether the User is allowed to update
     *
     * @param Questionnaire $questionnaire
     *
     * @return bool
     */
    protected function isAllowedQuestionnaire(Questionnaire $questionnaire)
    {
        // @todo remove me once login will be better handled GUI wise
        return true;

        /* @var $rbac \Application\Service\Rbac */
        $rbac = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac');
        return $rbac->isGrantedWithContext(
                        $questionnaire, Permission::CAN_CREATE_OR_UPDATE_ANSWER, new SurveyAssertion($questionnaire)
        );
    }

    /**
     * Ask Rbac whether the User is allowed to update
     *
     * @param Survey $survey
     *
     * @return bool
     */
    protected function isAllowedSurvey(Survey $survey)
    {
        // @todo remove me once login will be better handled GUI wise
        return true;

        /* @var $rbac \Application\Service\Rbac */
        $rbac = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac');
        return $rbac->isGrantedWithContext(
                        $survey, Permission::CAN_CREATE_OR_UPDATE_ANSWER, new SurveyAssertion($survey)
        );
    }

}

