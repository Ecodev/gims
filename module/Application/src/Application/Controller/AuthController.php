<?php

namespace Application\Controller;

use Application\Utility;
use Zend\Http\Response;
use Zend\View\Model\JsonModel;
use Zend\Json\Json;

class AuthController extends \ZfcUser\Controller\UserController
{

    use \Application\Traits\EntityManagerAware;

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

    protected function updateFirstAndLastLoginDate()
    {
        /* @var \Application\Model\User $user */
        $user = $this->zfcUserAuthentication()->getIdentity();
        $date = new \DateTime();
        if (!$user->getFirstLogin()) {
            $user->setFirstLogin($date);
        }
        $user->setLastLogin($date);
        $this->getEntityManager()->flush();
    }

    /**
     * Login form
     */
    public function loginAction($data = null)
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = $data ? : Json::decode($request->getContent(), Json::TYPE_ARRAY);

            // Copy data to POST values of request so ZfcUser authentication can found them
            foreach ($data as $key => $value) {
                if ($key == 'identity') {
                    $value = strtolower($value);
                }

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
            $this->updateFirstAndLastLoginDate();

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
            $data['email'] = strtolower($data['email']);
            $data['display_name'] = @$data['name'];

            $user = $this->getUserService()->register($data);

            // If registered successfully, send email activation
            if ($user) {
                Utility::executeCliCommand('email', 'activationLink', $user->getId());

                return new JsonModel(array('email' => $user->getEmail()));
            } else {
                $this->getResponse()->setStatusCode(400);

                return new JsonModel(array('message' => $this->getUserService()->getRegisterForm()->getMessages()));
            }
        }
    }

}
