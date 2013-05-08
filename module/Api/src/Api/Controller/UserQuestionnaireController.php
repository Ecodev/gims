<?php

namespace Api\Controller;

use Application\Assertion\QuestionnaireAssertion;
use Application\Model\Questionnaire;
use Zend\View\Model\JsonModel;

class UserQuestionnaireController extends AbstractRestfulController
{

    /**
     * @var \Application\Model\User
     */
    protected $user;

    /**
     * Get the user
     * @return \Application\Model\User
     */
    protected function getUser()
    {
        $idUser = $this->params('idUser');
        if (!$this->user && $idUser) {
            $userRepository = $this->getEntityManager()->getRepository('Application\Model\User');
            $this->user = $userRepository->find($idUser);
        }

        return $this->user;
    }

    /**
     * @return array
     */
    protected function getJsonConfig()
    {
        return array_merge(
            array(
                'questionnaire' => array(
                    'name',
                ),
                'role' => array(
                    'name',
                ),
                'user' => array(
                    'name',
                ),
            ),
            parent::getJsonConfig()
        );
    }


    public function getList()
    {
        $user = $this->getUser();

        // Cannot list all userQuestionnaire, without specifying a user
        if (!$user) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $userQuestionnaires = $this->getRepository()->findByUser($user);

        return new JsonModel($this->arrayOfObjectsToArray($userQuestionnaires, $this->getJsonConfig()));
    }

    /**
     * @param array $data
     *
     * @return mixed|void|JsonModel
     * @throws \Exception
     */
    public function create($data)
    {

        $questionnaire = new Questionnaire();
        $questionnaire->updateProperties($data);

        // Update object or not...
        if ($this->isAllowed($questionnaire)) {
            $result = parent::create($data);
        } else {
            $this->getResponse()->setStatusCode(401);
            $result = new JsonModel(array('message' => 'Authorization required'));
        }
        return $result;
    }

    /**
     * @param int   $id
     * @param array $data
     *
     * @return mixed|JsonModel
     */
    public function update($id, $data)
    {
        // Retrieve questionnaire since permissions apply against it.
        $repository = $this->getEntityManager()->getRepository($this->getModel());

        /** @var $questionnaire \Application\Model\Answer */
        $questionnaire = $repository->findOneById($id);

        // Update object or not...
        if ($this->isAllowed($questionnaire)) {
            $result = parent::update($id, $data);
        } else {
            $this->getResponse()->setStatusCode(401);
            $result = new JsonModel(array('message' => 'Authorization required'));
        }
        return $result;
    }

    /**
     * @param int $id
     *
     * @return mixed|JsonModel
     */
    public function delete($id)
    {

        // Retrieve questionnaire since permissions apply against it.
        $repository = $this->getEntityManager()->getRepository($this->getModel());

        /** @var $questionnaire \Application\Model\Answer */
        $questionnaire = $repository->findOneById($id);

        // Update object or not...
        if (is_null($questionnaire)) {
            $this->getResponse()->setStatusCode(404);
            $result = new JsonModel(array('message' => 'No object found'));
        } elseif ($this->isAllowed($questionnaire)) {
            $result = parent::delete($id);
        } else {
            $this->getResponse()->setStatusCode(401);
            $result = new JsonModel(array('message' => 'Authorization required'));
        }
        return $result;
    }

    /**
     * Ask Rbac whether the User is allowed to update
     *
     * @param Questionnaire $questionnaire
     *
     * @return bool
     */
    protected function isAllowed(Questionnaire $questionnaire)
    {
        // @todo remove me once login will be better handled GUI wise
        return true;

        /* @var $rbac \Application\Service\Rbac */
        $rbac = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac');
        return $rbac->isGrantedWithContext(
            $questionnaire,
            Permission::CAN_MANAGE_ANSWER,
            new QuestionnaireAssertion($questionnaire)
        );
    }
}
