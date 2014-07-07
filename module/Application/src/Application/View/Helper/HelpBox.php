<?php

namespace Application\View\Helper;

class HelpBox extends \Zend\View\Helper\AbstractHtmlElement
{

    /**
     * Returns Angular template to show help button
     * @param string $content
     * @return string
     */
    public function __invoke($content)
    {
        $result = <<<STRING
<div class="alert alert-info alert-dismissible ng-trans ng-trans-fade-up" ng-if="showHelp">
    <p><i class="fa fa-gims-help"></i> $content</p>
</div>
STRING;

        return $result;
    }

}
