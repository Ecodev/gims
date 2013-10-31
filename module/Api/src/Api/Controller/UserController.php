<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

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
    public function create($data)
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


    public function getList()
    {
        $q = $this->params()->fromQuery('q');
        $users = $this->getRepository()->getAllWithPermission($this->params()->fromQuery('permission', 'read'), 'user', null, $q);

        return new JsonModel($this->hydrator->extractArray($users, $this->getJsonConfig()));
    }


    public function delete($id)
    {
        throw new \Exception('Not implemtented ! see https://support.ecodev.ch/issues/2042');
    }

}
