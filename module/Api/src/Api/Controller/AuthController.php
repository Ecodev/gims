<?php

namespace Api\Controller;

use Zend\Http\Response;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

class AuthController extends \ZfcUser\Controller\UserController
{

    /**
     * Return the user info if logged in
     */
    protected function getUserInfo()
    {
        if ($this->zfcUserAuthentication()->getAuthService()->hasIdentity()) {
            return array(
                'status' => 'logged',
                'email' => $this->zfcUserAuthentication()->getIdentity()->getEmail(),
                'id' => $this->zfcUserAuthentication()->getIdentity()->getId(),
                'userName' => $this->zfcUserAuthentication()->getIdentity()->getUsername(),
                'displayName' => $this->zfcUserAuthentication()->getIdentity()->getDisplayname(),
                'gravatar' => $this->zfcUserAuthentication()->getIdentity()->getGravatar()
            );
        }
        return false;
    }

    /**
     * Login form
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();

        if ($request->isPost()) {
            $data = Json::decode($request->getContent(), Json::TYPE_ARRAY);

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
        if ($userInfo)
        {
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
        }
        elseif ($userInfo = $this->getUserInfo())
        {
            return new JsonModel($userInfo);
        }

    }

}
