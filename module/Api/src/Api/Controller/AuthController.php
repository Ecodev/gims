<?php

namespace Api\Controller;

use Zend\Http\Response;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

class AuthController extends \ZfcUser\Controller\UserController
{

    /**
     * Return the user info if logged in
     * @return array|false
     */
    protected function getUserInfo()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();

        if ($user) {
            $hydrator = new \Application\Service\Hydrator();
            $result = $hydrator->extract($user, array(
                'name',
                'email',
                'gravatar',
            ));
            $result['status'] = 'logged';

            return $result;
        }

        return false;
    }

    /**
     * Login form
     */
    public function loginAction($data = null)
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = $data ?: Json::decode($request->getContent(), Json::TYPE_ARRAY);

            // Copy data to POST values of request so ZfcUser authentication can found them
            foreach ($data as $key => $value) {
                $this->getRequest()->getPost()->set($key, $value);
            }

            $form = $this->getLoginForm();
            $form->setData($data);

            if (!$form->isValid()) {
                return new JsonModel(array(
                    'status' => 'failed',
                    'messages' => $form->getMessages()
                ));
            }
        }

        // check if the user is already logged in and return user info
        $userInfo = $this->getUserInfo();
        if ($userInfo) {
            return new JsonModel($userInfo);
        }

        // clear adapters
        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

        $adapter = $this->zfcUserAuthentication()->getAuthAdapter();

        $result = $adapter->prepareForAuthentication($this->getRequest());

        // Return early if an adapter returned a response
        if ($result instanceof Response) {
            return new JsonModel(array(
                'status' => 'failed',
                'messages' => 'Server DB adapter error'
            ));
        }

        $auth = $this->zfcUserAuthentication()->getAuthService()->authenticate($adapter);

        if (!$auth->isValid()) {
            return new JsonModel(array(
                'status' => 'failed',
                'messages' => 'Invalid username or password'
            ));
        } else {
            return new JsonModel($this->getUserInfo());
        }
    }

    /**
     * Register new user
     */
    public function registerAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = Json::decode($request->getContent(), Json::TYPE_ARRAY);
            $data['display_name'] = @$data['name'];

            $user = $this->getUserService()->register($data);

            if ($user) {
                $data['identity'] = $data['email'];
                $data['credential'] = $data['password'];

                return $this->loginAction($data);
            } else {
                $this->getResponse()->setStatusCode(400);

                return new JsonModel(array('message' => $this->getUserService()->getRegisterForm()->getMessages()));
            }
        }
    }

}
