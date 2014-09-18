angular.module('myApp.directives').directive('gimsCellMenu', function($dropdown, TableFilter) {

    return {
        restrict: 'E',
        template: '<span class="input-group-btn">' +
                '    <button type="button" class="btn btn-default dropdown-toggle">' +
                '        <span ng-switch="getCellType(questionnaire, filter, part.id)">' +
                '            <i ng-switch-when="loading" class="fa fa-fw fa-gims-loading"></i>' +
                '            <i ng-switch-when="error" class="fa fa-fw fa-warning text-warning"></i>' +
                '            <i ng-switch-when="answer" class="fa fa-fw fa-question"></i>' +
                '            <i ng-switch-when="rule" class="fa fa-fw fa-gims-rule" ng-class="{\'text-primary\': tabs.isComputing}"></i>' +
                '            <i ng-switch-when="summand" class="fa fa-fw fa-gims-summand" ng-class="{\'text-primary\': tabs.isComputing}"></i>' +
                '            <i ng-switch-when="child" class="fa fa-fw fa-gims-child" ng-class="{\'text-primary\': tabs.isComputing}"></i>' +
                '            <i ng-switch-default class="fa fa-fw fa-angle-down"></i>' +
                '        </span>' +
                '    </button>' +
                '</span>',
        replace: true,
        scope: {
            tabs: '=',
            mode: '=',
            questionnaire: '=',
            filter: '=',
            part: '='
        },
        link: function(scope, element) {

            scope.getCellType = TableFilter.getCellType;

            var button = element.find('button');
            button.bind('click', function() {
                $dropdown.open({
                    button: button,
                    templateUrl: '/template/browse/cell-menu/menu',
                    controller: 'CellMenuCtrl',
                    resolve: {
                        tabs: function() {
                            return scope.tabs;
                        },
                        mode: function() {
                            return scope.mode;
                        },
                        questionnaire: function() {
                            return scope.questionnaire;
                        },
                        filter: function() {
                            return scope.filter;
                        },
                        part: function() {
                            return scope.part;
                        }
                    }
                });
            });

        }
    };
});