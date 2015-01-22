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
     *
     * @param \ZfcUser\Service\User $userService
     *
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
        if (!isset($data['password'])) {
            $data['password'] = $this->generateRandomPassword();
            $data['passwordVerify'] = $data['password'];
        }

        $data['display_name'] = $data['name'];
        $user = $this->getUserService()->register($data);

        if ($user) {
            unset($data['password']);
            $this->hydrator->hydrate($data, $user);
            $this->getEntityManager()->flush();
            $this->getResponse()->setStatusCode(201);

            return new JsonModel($this->hydrator->extract($user, $this->getJsonConfig()));
        } else {
            $this->getResponse()->setStatusCode(400);

            return new JsonModel(['message' => $this->getUserService()->getRegisterForm()->getMessages()]);
        }
    }

    private function generateRandomPassword()
    {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = []; //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }

        return implode($pass); //turn the array into a string
    }

    public function statisticsAction()
    {
        $user = $this->getRepository()->findOneById($this->params('idUser'));

        if (!$user) {
            $this->getResponse()->setStatusCode(404);

            return new JsonModel(['message' => 'No object found']);
        }

        $stats = $this->getRepository()->getStatistics($user);

        return new JsonModel($stats);
    }

    public function loginAction()
    {
        $request = $this->getRequest();
        $form = $this->getLoginForm();

        if ($this->getOptions()->getUseRedirectParameterIfPresent() && $request->getQuery()->get('redirect')) {
            $redirect = $request->getQuery()->get('redirect');
        } else {
            $redirect = false;
        }

        if (!$request->isPost()) {
            return [
                'loginForm' => $form,
                'redirect' => $redirect,
                'enableRegistration' => $this->getOptions()->getEnableRegistration(),
            ];
        }

        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage($this->failedLoginMessage);

            return $this->redirect()->toUrl($this->url()->fromRoute(static::ROUTE_LOGIN) . ($redirect ? '?redirect=' . $redirect : ''));
        }

        // clear adapters
        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

        return $this->forward()->dispatch(static::CONTROLLER_NAME, ['action' => 'authenticate']);
    }

    public function delete($id)
    {
        throw new \Exception('Not implemtented ! see https://support.ecodev.ch/issues/2042');
    }

    public function activateAction()
    {
        $token = $this->getRequest()->getQuery()->get('token');
        $user = $this->getRepository()->findOneByToken($token);
        if ($user) {

            if ($this->checkTokenValidity($user)) {
                $user->setState(1);
                $this->getEntityManager()->flush();

                return new JsonModel($this->hydrator->extract($user, $this->getJsonConfig()));
            } else {
                $this->getResponse()->setStatusCode(403);

                return new JsonModel(['message' => 'Activation delay timed out.']);
            }
        } else {
            $this->getResponse()->setStatusCode(404);

            return new JsonModel(['message' => 'This activation token is invalid. No user could be found.']);
        }
    }

    public function changePasswordAction()
    {
        $token = $this->getRequest()->getQuery()->get('token');
        $pass1 = $this->getRequest()->getQuery()->get('pass1');
        $pass2 = $this->getRequest()->getQuery()->get('pass2');
        $user = $this->getRepository()->findOneByToken($token);

        if ($user) {
            if ($pass1 == $pass2) {
                $user->setState(1);
                $user->setPassword($pass1);
                $this->getEntityManager()->flush();

                return new JsonModel($this->hydrator->extract($user, $this->getJsonConfig()));
            } else {
                return new JsonModel(['message' => "Passwords don't match."]);
            }
        } else {
            $this->getResponse()->setStatusCode(404);

            return new JsonModel(['message' => 'User has not been found']);
        }
    }

    /**
     * Check if token is younger than 15 min.
     * @return JsonModel
     */
    public function checkTokenValidityAction()
    {
        $token = $this->getRequest()->getQuery()->get('token');
        $user = $this->getRepository()->findOneByToken($token);

        if (!$user) {
            $this->getResponse()->setStatusCode(404);

            return new JsonModel(['message' => 'No user has been found']);
        }

        if ($this->checkTokenValidity($user)) {
            return new JsonModel($this->hydrator->extract($user, $this->getJsonConfig()));

        } else {
            $this->getResponse()->setStatusCode(403);

            return new JsonModel(['message' => 'Link validity timed out.']);
        }
    }

    /**
     * Check if token is younger than 15 min.
     * @param \Application\Model\User $user
     * @return bool
     */
    public function checkTokenValidity(\Application\Model\User $user)
    {
        $dateNow = new \DateTime();
        $dateTokenGenerated = $user->getDateTokenGenerated();
        $delay = 15 * 60;

        if (($dateNow->getTimestamp() - $dateTokenGenerated->getTimestamp()) < $delay) {
            return true;
        } else {
            return false;
        }
    }

}
