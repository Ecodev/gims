<?php

namespace Application\View\Helper;

class Metadata extends \Zend\View\Helper\AbstractHtmlElement
{

    /**
     * Returns Angular template to show metadata of $objectName
     * @param string $objectName
     * @return string
     */
    public function __invoke($objectName)
    {
        $result = <<<STRING
    <div class="row form-metadata">
        <p class="col-md-6">
            <span ng-class="{'hide': !$objectName.dateCreated}">
                Created on {{{$objectName}.dateCreated | date:'dd of MMM yyyy @ HH:mm'}}
            </span>
            <span ng-class="{'hide': !$objectName.creator}">
                by {{{$objectName}.creator.name}}
            </span>
        </p>
        <p class="col-md-6">
            <span ng-class="{'hide': !$objectName.dateModified}">
                Updated on {{{$objectName}.dateModified | date:'dd of MMM yyyy @ HH:mm'}}
            </span>
            <span ng-class="{'hide': !$objectName.modifier}">
                by {{{$objectName}.modifier.name}}
            </span>
        </p>
    </div>
STRING;

        return $result;
    }
}
