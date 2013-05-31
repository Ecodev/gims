<?php

namespace Application\View\Helper;

class Api extends \Zend\View\Helper\AbstractHtmlElement
{

    /**
     * Returns metadata
     * @param string $route
     * @return string
     */
    public function __invoke($route)
    {
        $result = file_get_contents('http://fab.gims.pro/api/' . $route);
        return $result;
    }

}
