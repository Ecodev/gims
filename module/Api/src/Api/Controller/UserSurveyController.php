<?php

namespace Api\Controller;

use Application\Model\UserSurvey;
use Zend\View\Model\JsonModel;

class UserSurveyController extends AbstractRestfulController
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
        $parent = $this->getParent();

        // Cannot list all userSurvey, without specifying a user
        if (!$parent) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $userSurveys = $this->getRepository()->findBy(array($this->params('parent') => $parent));

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
