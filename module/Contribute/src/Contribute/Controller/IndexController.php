<?php

namespace Contribute\Controller;

use Zend\View\Model\ViewModel;

class IndexController extends \Application\Controller\AbstractAngularActionController
{

    public function indexAction()
    {
        return new ViewModel([
            'registerForm' => $this->getServiceLocator()->get('zfcuser_register_form'),
        ]);
    }

    public function glaasAction()
    {
        return new ViewModel();
    }
}
