<?php

namespace Browse\Controller;

use Zend\View\Model\ViewModel;

class CellMenuController extends \Application\Controller\AbstractAngularActionController
{

    public function menuAction()
    {
        return new ViewModel();
    }

    public function windowAction()
    {
        return new ViewModel();
    }

}
