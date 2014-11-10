<?php

namespace Browse\Controller;

use Zend\View\Model\ViewModel;

class DiscussionController extends \Application\Controller\AbstractAngularActionController
{

    public function menuAction()
    {
        return new ViewModel();
    }

    public function modalAction()
    {
        return new ViewModel();
    }

}
