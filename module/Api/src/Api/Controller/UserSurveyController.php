<?php

namespace Api\Controller;

use Application\Assertion\SurveyAssertion;
use Application\Model\UserSurvey;
use Zend\View\Model\JsonModel;

class UserSurveyController extends AbstractRestfulController
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
            'survey' => array(
                'name',
            ),
            'role' => array(
                'name',
            ),
            'user' => array(
                'name',
            ),
                ), parent::getJsonConfig()
        );
    }

    public function getList()
    {
        $user = $this->getUser();

        // Cannot list all userSurvey, without specifying a user
        if (!$user) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $userSurveys = $this->getRepository()->findByUser($user);

        return new JsonModel($this->hydrator->extractArray($userSurveys, $this->getJsonConfig()));
    }

    /**
     * @param array $data
     *
     * @return mixed|void|JsonModel
     * @throws \Exception
     */
    public function create($data)
    {
        $userSurvey = new UserSurvey();

        // Update object or not...
        if ($this->isAllowed($userSurvey)) {
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
        throw new \Exception('Not implemented');
    }

    /**
     * @param int $id
     *
     * @return mixed|JsonModel
     */
    public function delete($id)
    {
        $userSurvey =  $this->getRepository()->findOneById($id);

        // Update object or not...
        if (is_null($userSurvey)) {
            $this->getResponse()->setStatusCode(404);
            $result = new JsonModel(array('message' => 'No object found'));
        } elseif ($this->isAllowed($userSurvey)) {
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
     * @param UserSurvey $userSurvey
     *
     * @return bool
     */
    protected function isAllowed(UserSurvey $userSurvey)
    {
        // @todo remove me once login will be better handled GUI wise
        return true;

        /* @var $rbac \Application\Service\Rbac */
        $rbac = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac');
        return $rbac->isGranted(Permission::CAN_CREATE_OR_UPDATE_ANSWER);
    }

}
