<?php

namespace Admin\Controller;

use Zend\View\Model\ViewModel;

class UserController extends \Application\Controller\AbstractAngularActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }

    public function crudAction()
    {
        return new ViewModel();
    }

}
