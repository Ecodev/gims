<?php

namespace Api\Controller;

use Application\Assertion\QuestionnaireAssertion;
use Application\Model\Questionnaire;
use Zend\View\Model\JsonModel;

class UserQuestionnaireController extends AbstractRestfulController
{

    /**
     * @var \Application\Model\AbstractModel
     */
    protected $parent;

    /**
     * Get the parent, either User, Survey, or Role
     * @return \Application\Model\AbstractModel
     */
    protected function getParent()
    {
        $id = $this->params('idParent');
        if (!$this->parent && $id) {
            $userRepository = $this->getEntityManager()->getRepository('Application\Model\\' . ucfirst($this->params('parent')));
            $this->parent = $userRepository->find($id);
        }

        return $this->parent;
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
        $parent = $this->getParent();

        // Cannot list all userQuestionnaires, without a parent
        if (!$parent) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $userQuestionnaires = $this->getRepository()->findBy(array($this->params('parent') => $parent));

        return new JsonModel($this->hydrator->extractArray($userQuestionnaires, $this->getJsonConfig()));
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
        $this->hydrator->hydrate($data, $questionnaire);

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
            Permission::CAN_CREATE_OR_UPDATE_ANSWER,
            new QuestionnaireAssertion($questionnaire)
        );
    }
}
