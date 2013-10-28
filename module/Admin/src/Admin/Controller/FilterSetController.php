<?php

namespace Admin\Controller;

use Zend\View\Model\ViewModel;

class FilterSetController extends \Application\Controller\AbstractAngularActionController
{

    public function indexAction()
    {
        return new ViewModel();
    }

}
