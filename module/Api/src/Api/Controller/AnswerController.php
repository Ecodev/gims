<?php

namespace Api\Controller;

use Application\Assertion\QuestionnaireAssertion;
use Application\Model\Permission;
use Application\Model\Questionnaire;
use Zend\View\Model\JsonModel;

class AnswerController extends AbstractRestfulController
{
    /**
     * @return mixed|JsonModel
     */
    public function getList()
    {
        $idQuestionnaire = $this->params('idQuestionnaire');
        $c = array(
            'questionnaire' => $idQuestionnaire,
        );

        $objects = $this->getRepository()->findBy($c);
        return new JsonModel($this->arrayOfObjectsToArray($objects, $this->getJsonConfig()));
    }


    /**
     * @param array $data
     *
     * @return mixed|void|JsonModel
     * @throws \Exception
     */
    public function create($data)
    {
        // questionnaire value is mandatory
        if (empty($data['questionnaire'])) {
            throw new \Exception('Missing questionnaire value', 1365598940);
        }

        // Retrieve a questionnaire from the storage
        $repository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');
        $questionnaire = $repository->findOneById($data['questionnaire']);

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
        /** @var $answer \Application\Model\Answer */
        $repository = $this->getEntityManager()->getRepository($this->getModel());
        $answer = $repository->findOneById($id);
        $questionnaire = $answer->getQuestionnaire();

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
     * @return array
     */
    protected function getJsonConfig()
    {
        return array(
            'valuePercent',
            'valueAbsolute',
            'part'     => array(
                'name',
            ),
            'question' => array(
                'name',
                'filter' => array(
                    'name'
                ),
            )
        );
    }

    /**
     * Ask Rbac whether the User is allowed to update
     *
     * @param \Application\Model\Questionnaire $questionnaire
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
