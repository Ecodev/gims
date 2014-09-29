<?php

namespace Application\View\Helper;

class HelpButton extends \Zend\View\Helper\AbstractHtmlElement
{

    /**
     * Returns Angular template to show help button
     * @return string
     */
    public function __invoke()
    {
        $result = '<a href class="pull-right" ng-click="showHelp = !showHelp"><i tooltip="Help" class="fa fa-fw fa-gims-help"></i></a>';

        return $result;
    }

}
