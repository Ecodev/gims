<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

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
