<?php

namespace Application\View\Helper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;

class BodyCssClass extends \Zend\View\Helper\AbstractHtmlElement implements ServiceLocatorAwareInterface
{

    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

    /**
     * Returns an HTML class attribute for <body> element based on configuration file
     * @return string
     */
    public function __invoke()
    {
        $class = $this->getServiceLocator()->getServiceLocator()->get('Config')['bodyCssClass'];

        if ($class) {
            return ' class="' . $class . '"';
        } else {
            return '';
        }
    }
}
