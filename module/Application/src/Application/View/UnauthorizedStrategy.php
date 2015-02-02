<?php

namespace Application\View;

use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class UnauthorizedStrategy extends \ZfcRbac\View\Strategy\UnauthorizedStrategy
{

    /**
     * Override parent to work well with angular-http-auth
     *
     * @param  MvcEvent $event
     * @return void
     */
    public function onError(MvcEvent $event)
    {
        parent::onError($event);

        $response = $event->getResponse();
        $result = $event->getResult();

        // Do nothing if response is not HTTP response
        if (!($response instanceof HttpResponse)) {
            return;
        }

        // If the identity is not a real user, it means we are not logged in,
        // thus we return a 401 to tell angular to prompt for login
        if ($response->getStatusCode() == 403 && !$event->getParam('identity') instanceof \Application\Model\User) {
            $response->setStatusCode(401);
        }

        if ($result instanceof ViewModel) {
            $result->setVariable('statusCode', $response->getStatusCode());
        }
    }
}
