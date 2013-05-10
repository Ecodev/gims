<?php

namespace Api\Controller;

use ZfcUser\Controller\UserController;

class AuthController extends ZfcUser\Controller\UserController;
{
    /**
     * Login form
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();
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
            $reponse->setStatusCode(Response::STATUS_CODE_401); // Failed login attempt
            return array(
                'message' => $this->failedLoginMessage,
            );
        }

        // clear adapters
        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

        return $this->forward()->dispatch(static::CONTROLLER_NAME, array('action' => 'authenticate'));
    }

}
