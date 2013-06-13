<?php

namespace Application\View\Helper;

class CrudButtons extends \Zend\View\Helper\AbstractHtmlElement
{

    /**
     * Returns Angular template to show CRUD buttons
     * @param string $objectName
     * @return string
     */
    public function __invoke($objectName)
    {
        $result = <<<STRING

        <a ng-click="cancel()" class="btn">Back</a>

        <span ng-switch="sending > 0">
            <div class="btn-group" ng-switch-when="true">
                <button class="btn btn-primary disabled btn-saving">
                    <i class="icon-loading"></i>
                    <ng-pluralize count="sending + 0" when="{'one': 'Saving', 'other': 'Saving {} items'}" />
                </button>
                <button class="btn btn-primary dropdown-toggle disabled" data-toggle="dropdown">
                    <span class="caret"></span>
                </button>
            </div>

            <div class="btn-group" ng-switch-when="false">
                <button class="btn btn-primary btn-save" ng-click="save()" ng-disabled="myForm.\$invalid"><i class="icon-ok"></i> Save</button>
                <button class="btn btn-primary dropdown-toggle" ng-disabled="myForm.\$invalid" data-toggle="dropdown">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a ng-click="saveAndClose()">Save and close</a>
                    </li>
                </ul>
            </div>
        </span>

        <button ng-click="delete()"
                ng-show="$objectName.id" class="btn btn-danger"><i class="icon-trash"></i> Delete</button>
        <!-- && $objectName.permission.canBeDeleted -->
STRING;

        return $result;
    }

}
