angular.module('myApp.directives').directive('gimsGridQuestion', function() {
    'use strict';

    return {
        restrict: 'A', // Only usage possible is with attribute
        // This HTML will replace the directive.
        replace: true,
        transclude: true,
        template: '<div>' +
                '<div class="row show-grid">' +
                '<div class="col-md-7">' +
                '<input type="text" ng-model="gridOptions.filterOptions.filterText" placeholder="Search..." class="search form-control" style="width: 400px"/>' +
                '</div>' +
                '<div class="col-md-5" style="text-align: right">' +
                '<gims-link-new origin="survey" target="question" return-tab="1"/>' +
                '</div>' +
                '</div>' +
                '<gims-grid api="question" parent="survey" options="gridOptions" queryparams="queryparams"></gims-grid>' +
                '</div>',
        // The linking function will add behavior to the template
        link: function() {
            // nothing to do ?
        },
        controller: function($scope, $location) {

            // Configure ui-grid.
            $scope.queryparams = {fields: 'type,chapter'};
            $scope.gridOptions = {
                extra: {},
                columnDefs: [
                    {
                        field: 'sorting',
                        displayName: '#',
                        width: '10%',
                        cellTemplate: '<div class="ui-grid-cell-contents" ng-class="col.colIndex()">' +
                                '<span style="padding-left: {{row.entity.level}}em;">{{row.entity.sorting}}</span>' +
                                '</div>'
                    },
                    {
                        field: 'name',
                        displayName: 'Question',
                        width: '80%',
                        cellTemplate: '<div class="ui-grid-cell-contents" ng-class="col.colIndex()">' +
                                '<span style="padding-left: {{row.entity.level}}em;">{{row.entity.name}}</span>' +
                                '</div>'
                    },
                    {
                        name: 'buttons',
                        displayName: '',
                        width: '10%',
                        cellTemplate: '<div class="btn-group" style="margin-top:4px;margin-left:4px">' +
                                '<a class="btn btn-default btn-xs btn-edit" href="/admin/question/edit/{{row.entity.id}}?returnUrl={{options.extra.returnUrl}}">' +
                                '<i class="fa fa-pencil fa-lg"></i>' +
                                '</a>' +
                                '<button type="button" class="btn btn-default btn-xs" ng-click="getExternalScopes().remove(row)" >' +
                                '<i class="fa fa-trash-o fa-lg"></i>' +
                                '</button>' +
                                '</div>'

                    }
                ]
            };

            $scope.$watch(function() {
                return $location.url();
            }, function() {
                $scope.gridOptions.extra.returnUrl = encodeURIComponent($location.url());
            });
        }
    };
});
