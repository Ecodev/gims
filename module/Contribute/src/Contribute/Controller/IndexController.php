<?php

namespace Contribute\Controller;

use Zend\View\Model\ViewModel;

class IndexController extends \Application\Controller\AbstractAngularActionController
{

    public function indexAction()
    {
        return new ViewModel(array(
            'registerForm' => $this->getServiceLocator()->get('zfcuser_register_form'),
        ));
    }

    public function glassAction()
    {
        return new ViewModel();
    }

}
