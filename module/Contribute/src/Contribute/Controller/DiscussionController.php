<?php

namespace Contribute\Controller;

use Zend\View\Model\ViewModel;

class DiscussionController extends \Application\Controller\AbstractAngularActionController
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
