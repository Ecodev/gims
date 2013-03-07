<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

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

    public function browseAction()
    {
        return new ViewModel();
    }

    public function contributeAction()
    {
        return new ViewModel(array(
            'registerForm' => $this->getServiceLocator()->get('zfcuser_register_form'),
        ));
    }

}
