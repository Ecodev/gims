<?php

namespace Browse\Controller;

use Zend\View\Model\ViewModel;

class ChartController extends \Application\Controller\AbstractAngularActionController
{

    /**
     * @return ViewModel
     */
    public function indexAction()
    {
        return new ViewModel();
    }

    /**
     * @return ViewModel
     */
    public function sectorAction()
    {
        return new ViewModel();
    }

}
