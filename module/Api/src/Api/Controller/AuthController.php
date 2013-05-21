<?php

namespace Api\Controller;

use Zend\Http\Response;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

class AuthController extends \ZfcUser\Controller\UserController
{

    /**
     * Login form
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();
        $form = $this->getLoginForm();

        if ($this->getOptions()->getUseRedirectParameterIfPresent() && $request->getQuery()->get('redirect')) {
            $redirect = $request->getQuery()->get('redirect');
        } else {
            $redirect = false;
        }

        if ($request->isPost()) {
            $data = Json::decode($request->getContent(), Json::TYPE_ARRAY);

            // Copy data to POST values of request so ZfcUser authentification can found them
            foreach ($data as $key => $value) {
                $this->getRequest()->getPost()->set($key, $value);
            }

            $form->setData($data);

            if (!$form->isValid()) {
                $response->setStatusCode(Response::STATUS_CODE_401); // Failed login attempt
                return new JsonModel($form->getMessages());
            }
        }

        // clear adapters
        $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

        return $this->authenticateAction();
    }

    /**
     * General-purpose authentication action
     */
    public function authenticateAction()
    {
        $response = $this->getResponse();

        if ($this->zfcUserAuthentication()->getAuthService()->hasIdentity()) {
            return new JsonModel(array(
                'status' => 'success'
            ));
        }
        $adapter = $this->zfcUserAuthentication()->getAuthAdapter();

        $result = $adapter->prepareForAuthentication($this->getRequest());

        // Return early if an adapter returned a response
        if ($result instanceof Response) {
            $response->setStatusCode(Response::STATUS_CODE_401);
            return new JsonModel(array(
                'status' => 'adapter failed'
            ));
        }

        $auth = $this->zfcUserAuthentication()->getAuthService()->authenticate($adapter);

        if (!$auth->isValid()) {
            $response->setStatusCode(Response::STATUS_CODE_401);
            return new JsonModel(array(
                'status' => 'failed'
            ));
        }

        return new JsonModel(array(
            'status' => 'success'
        ));
    }

}
