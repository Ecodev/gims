angular.module('myApp.directives').directive('gimsGridQuestion', function () {
    'use strict';

    return {
        restrict: 'A', // Only usage possible is with attribute
        // This HTML will replace the directive.
        replace: true,
        transclude: true,
        template: '<div>' +
            '<div class="row-fluid">' +
            '<div class="span7">' +
            '<input type="text" ng-model="gridOptions.filterOptions.filterText" placeholder="Search..." class="search" style="width: 400px"/>' +
            '</div>' +
            '<div class="span5" style="text-align: right">' +
            '<i class="icon-plus-sign"></i> ' +
            '<gims-link-new origin="survey" target="question" return-tab="1"/>' +
            '</div>' +
            '</div>' +
            '<div ng-grid="gridOptions" class="gridStyle"></div>' +
            '</div>',
        // The linking function will add behavior to the template
        link: function () {
            // nothing to do ?
        },
        controller: function ($scope, $location, $resource, Modal) {

            // Edit a question
            $scope.edit = function (row) {
                var returnUrl = $location.path();
                $location.path('/admin/question/edit/' + row.entity.id)
                    .search({
                        'returnUrl': returnUrl,
                        'returnTab': 1
                    })
                    .hash(null);
            };

            // Delete a question
            $scope.removeQuestion = function (row) {
                var Question = new $resource('/api/question'); // TODO: find out a way to it with restangular instead of $resource
                var question = new Question(row.entity);
                Modal.confirmDelete(question, {objects: $scope.questions, label: question.name, returnUrl: $location.path()});
            };

            // Keep track of the selected row.
            $scope.selectedRow = [];

            // Configure ng-grid.
            $scope.gridOptions = {
                plugins: [new ngGridFlexibleHeightPlugin({minHeight: 100})],
                data: 'questions',
                enableCellSelection: true,
                showFooter: false,
                selectedItems: $scope.selectedRow,
                filterOptions: {},
                multiSelect: false,
                columnDefs: [
                    {
                        field: 'sorting',
                        displayName: '#',
                        width: '5%',
                        cellTemplate:   '<div class="ngCellText" ng-class="col.colIndex()">' +
                                            '<span style="padding-left: {{row.entity.level}}em;">{{row.entity.sorting}}</span>' +
                                        '</div>'
                    },
                    {
                        field: 'name',
                        displayName: 'Question',
                        width: '85%',
                        cellTemplate:   '<div class="ngCellText" ng-class="col.colIndex()">' +
                                            '<span style="padding-left: {{row.entity.level}}em;">{{row.entity.name}}</span>' +
                                        '</div>'
                    },
                    {
                        displayName: '',
                        width: '10%',
                        cellTemplate: '<button type="button" class="btn btn-mini btn-edit" ng-click="edit(row)" >' +
                                            '<i class="icon-pencil icon-large"></i></button>' +
                                            '<button type="button" class="btn btn-mini btn-remove" ng-click="removeQuestion(row)" >' +
                                            '<i class="icon-trash icon-large"></i>' +
                                        '</button>'
                    }
                ]
            };
        }
    };
});