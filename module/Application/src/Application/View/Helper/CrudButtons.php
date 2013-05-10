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

        <div class="btn-group">
            <button ng-click="save()" ng-disabled="myForm.\$invalid" class="btn btn-primary"
                    ng-class="{'disabled': sending}" ng-bind-html-unsafe="sendLabel"></button>
            <button class="btn btn-primary dropdown-toggle" ng-class="{'disabled': sending}" data-toggle="dropdown">
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a ng-click="saveAndClose()">Save and close</a>
                </li>
            </ul>
        </div>

        <a ng-click="cancel()" class="btn"><i class="icon-minus-sign"></i> Cancel</a>

        <button ng-click="remove()"
                ng-show="$objectName.id" class="btn btn-danger"><i class="icon-trash"></i> Delete
        </button>
STRING;

        return $result;
    }

}
