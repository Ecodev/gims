<?php

namespace Api\Controller;

use Application\Assertion\UserAssertion;
use Application\Model\User;
use Zend\View\Model\JsonModel;

class UserController extends AbstractRestfulController
{

    /**
     * @return array
     */
    protected function getJsonConfig()
    {
        return array_merge(array(
            'name',
            'email',
            'state',
                ), parent::getJsonConfig()
        );
    }

    /**
     * @param array $data
     *
     * @return mixed|void|JsonModel
     * @throws \Exception
     */
    public function create($data)
    {

        $user = new User();
        $this->hydrator->hydrate($data, $user);

        // Update object or not...
        if ($this->isAllowed($user)) {
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
        // Retrieve user since permissions apply against it.
        $repository = $this->getRepository();

        /** @var $user \Application\Model\User */
        $user = $repository->findOneById($id);

        // Update object or not...
        if ($this->isAllowed($user)) {
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

        // Retrieve user since permissions apply against it.
        $repository = $this->getEntityManager()->getRepository($this->getModel());

        /** @var $user \Application\Model\User */
        $user = $repository->findOneById($id);

        // Update object or not...
        if (is_null($user)) {
            $this->getResponse()->setStatusCode(404);
            $result = new JsonModel(array('message' => 'No object found'));
        } elseif ($this->isAllowed($user)) {
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
     * @param User $user
     *
     * @return bool
     */
    protected function isAllowed(User $user)
    {
        // @todo remove me once login will be better handled GUI wise
        return true;

        /* @var $rbac \Application\Service\Rbac */
        $rbac = $this->getServiceLocator()->get('ZfcRbac\Service\Rbac');
        return $rbac->isGrantedWithContext(
                        $user, Permission::CAN_MANAGE_ANSWER, new UserAssertion($user)
        );
    }

    public function statisticsAction()
    {
// TODO: make the user mandatory and return stats based on actual roles (once we can pass the ID from angular)
//        $user = $this->getRepository()->findOneById($this->params('idUser'));
//
//        if (!$user) {
//            $this->getResponse()->setStatusCode(404);
//            return new JsonModel(array('message' => 'No object found'));
//        }

        $stats = $this->getRepository()->getStatistics(/* $user */);

        return new JsonModel($stats);
    }

}
