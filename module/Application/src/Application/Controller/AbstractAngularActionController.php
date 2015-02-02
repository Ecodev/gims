<?php

namespace Application\Controller;

use Zend\Mvc\MvcEvent;

class AbstractAngularActionController extends \Zend\Mvc\Controller\AbstractActionController
{

    use \Application\Traits\EntityManagerAware;

    /**
     * Automatically disable layout rendering if the request is an ajax call
     *
     * @param  MvcEvent $e
     * @return mixed
     * @throws Exception\DomainException
     */
    public function onDispatch(MvcEvent $e)
    {
        $actionResponse = parent::onDispatch($e);
        $actionResponse->setTerminal($this->getRequest()->isXmlHttpRequest());

        return $actionResponse;
    }
}
