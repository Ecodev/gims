<?php

namespace Application\Controller;

use Application\Utility;
use Zend\Crypt\Password\Bcrypt;
use Zend\Http\Response;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;

class AuthController extends \ZfcUser\Controller\UserController
{

    use \Application\Traits\EntityManagerAware;

    /**
     * Extremely dirty way to retrieve ServiceManger. Highly discouraged to use it.
     * @return \Zend\ServiceManager\ServiceManager
     */
    public function getServiceLocator()
    {
        return \Application\Module::getServiceManager();
    }

    /**
     * Return the user info if logged in
     * @return array|false
     */
    protected function getUserInfo()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();

        if ($user) {
            $hydrator = new \Application\Service\Hydrator();
            $result = $hydrator->extract($user, [
                'name',
                'email',
                'gravatar',
                'firstLogin',
            ]);
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
                return new JsonModel([
                    'status' => 'failed',
                    'messages' => $form->getMessages(),
                ]);
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
            return new JsonModel([
                'status' => 'failed',
                'messages' => 'Server DB adapter error',
            ]);
        }

        $auth = $this->zfcUserAuthentication()->getAuthService()->authenticate($adapter);

        if (!$auth->isValid()) {
            return new JsonModel([
                'status' => 'failed',
                'messages' => 'Invalid username or password',
            ]);
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

                return new JsonModel(['email' => $user->getEmail()]);
            } else {
                $this->getResponse()->setStatusCode(400);

                return new JsonModel(['message' => $this->getUserService()->getRegisterForm()->getMessages()]);
            }
        }
    }

    /**
     * If GET request, send link to change password
     * If PUT request, update password.
     *
     * @return JsonModel
     */
    public function changepasswordAction()
    {
        $request = $this->getRequest();

        if ($request->isGet()) {
            return $this->sendResetPasswordLink($request);
        } elseif ($request->isPut()) {
            return $this->changePassword($request);
        }
    }

    /**
     * Send mail with link to reset password
     * @return JsonModel
     */
    private function sendResetPasswordLink()
    {
        $user = $this->getEntityManager()->getRepository('\Application\Model\User')->findOneByEmail(strtolower($this->params()->fromQuery('email')));

        // If registered successfully, send change password link
        if ($user) {
            Utility::executeCliCommand('email', 'changePasswordLink', $user->getId());

            return new JsonModel(['email' => $user->getEmail()]);
        } else {
            $this->getResponse()->setStatusCode(400);

            return new JsonModel(['message' => 'No user has been found']);
        }
    }

    /**
     * Save the new password
     * @param $request
     * @return JsonModel
     */
    private function changePassword($request)
    {
        $data = Json::decode($request->getContent(), Json::TYPE_ARRAY);
        $user = $this->getEntityManager()->getRepository('\Application\Model\User')->findOneByToken($data['token']);

        if ($user) {
            $bcrypt = new Bcrypt();
            $bcrypt->setCost($this->getOptions()->getPasswordCost());
            $pass = $bcrypt->create($data['password']);
            $user->setPassword($pass);
            $this->getEntityManager()->flush();

            $hydrator = new \Application\Service\Hydrator();

            return new JsonModel($hydrator->extract($user));
        } else {
            $this->getResponse()->setStatusCode(400);

            return new JsonModel(['message' => 'No user has been found']);
        }
    }
}
