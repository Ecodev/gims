<?php

namespace Api\Controller;

use Application\Assertion\UserAssertion;
use Application\Model\User;
use Zend\View\Model\JsonModel;
use ZfcUser\Controller\UserController as ZfcUser;

class UserController extends AbstractRestfulController
{

    /**
     * @var \ZfcUser\Service\User
     */
    protected $userService;

    /**
     * Get User Service
     * @return \ZfcUser\Service\User
     */
    public function getUserService()
    {
        if (!$this->userService) {
            $this->userService = $this->getServiceLocator()->get('zfcuser_user_service');
        }

        return $this->userService;
    }

    /**
     * Set User Service
     * @param \ZfcUser\Service\User $userService
     * @return \Api\Controller\UserController
     */
    public function setUserService(\ZfcUser\Service\User $userService)
    {
        $this->userService = $userService;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return mixed|void|JsonModel
     * @throws \Exception
     */
    public function create($data, \Closure $postAction = null)
    {
        $data['display_name'] = $data['name'];
        $user = $this->getUserService()->register($data);

        if ($user) {
            $this->getResponse()->setStatusCode(201);

            return new JsonModel($this->hydrator->extract($user, $this->getJsonConfig()));
        } else {
            $this->getResponse()->setStatusCode(400);

            return new JsonModel(array('message' => $this->getUserService()->getRegisterForm()->getMessages()));
        }
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
                        $user, Permission::CAN_CREATE_OR_UPDATE_ANSWER, new UserAssertion($user)
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

    public function loginAction()
    {
        $request = $this->getRequest();
        $form    = $this->getLoginForm();

        if ($this->getOptions()->getUseRedirectParameterIfPresent() && $request->getQuery()->get('redirect')) {
            $redirect = $request->getQuery()->get('redirect');
        } else {
            $redirect = false;
        }

        if (!$request->isPost()) {
            return array(
                'loginForm' => $form,
                'redirect'  => $redirect,
                'enableRegistration' => $this->getOptions()->getEnableRegistration(),
            );
        }

        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage($this->failedLoginMessage);

            return $this->redirect()->toUrl($this->url()->fromRoute(static::ROUTE_LOGIN).($redirect ? '?redirect='.$redirect : ''));
        }

        // clear adapters
        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

        return $this->forward()->dispatch(static::CONTROLLER_NAME, array('action' => 'authenticate'));
    }

}
