<?php

namespace Contribute\Controller;

use Zend\View\Model\ViewModel;

class ActivityController extends \Application\Controller\AbstractAngularActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }
}
