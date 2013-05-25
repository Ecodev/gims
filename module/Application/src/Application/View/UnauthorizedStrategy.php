<?php

namespace Application\View;

use ZfcRbac\Service\Rbac;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class UnauthorizedStrategy extends \ZfcRbac\View\UnauthorizedStrategy
{

    /**
     * Override parent to work well with angular-http-auth
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function prepareUnauthorizedViewModel(MvcEvent $e)
    {
        parent::prepareUnauthorizedViewModel($e);

        // Do nothing if the result is a response object
        $result = $e->getResult();
        if ($result instanceof Response) {
            return;
        }

        // Do nothing if our parent did nothing
        if (!in_array($e->getError(), array(
                    Rbac::ERROR_CONTROLLER_UNAUTHORIZED,
                    Rbac::ERROR_ROUTE_UNAUTHORIZED)) ||
                !$e->getResult() instanceof ViewModel) {
            return;
        }

        // If the identity is not a real user, it means we are not logged in,
        // thus we return a 401 to tell angular to prompt for login
        $response = $e->getResponse();
        if (!$e->getParam('identity') instanceof \Application\Model\User) {
            $response->setStatusCode(401);
        }

        $e->getResult()->setVariable('statusCode', $response->getStatusCode());
    }

}
