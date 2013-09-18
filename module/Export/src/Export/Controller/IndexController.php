<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Export\Controller;

use Zend\View\Model\ViewModel;

class IndexController extends \Application\Controller\AbstractAngularActionController
{

    /**
     * @return ViewModel
     */
    public function testAction()
    {
        return new ViewModel();
    }

}