<?php

namespace Application\Controller;

use Zend\View\Model\ViewModel;

class IndexController extends AbstractAngularActionController
{

    public function indexAction()
    {
        return new ViewModel(array(
            'registerForm' => $this->getServiceLocator()->get('zfcuser_register_form'),
        ));
    }

    public function homeAction()
    {
        return new ViewModel();
    }

    public function aboutAction()
    {
        return new ViewModel();
    }

    public function loginAction()
    {
        return new ViewModel();
    }

}
