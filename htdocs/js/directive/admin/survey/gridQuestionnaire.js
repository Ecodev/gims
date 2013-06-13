angular.module('myApp.directives').directive('gimsGridQuestionnaire', function () {
    'use strict';

    return {
        restrict: 'A', // Only usage possible is with attribute
        // This HTML will replace the directive.
        replace: true,
        transclude: true,
        template: '<div>' +
            '<div class="row-fluid">' +
            '<div class="span9">' +
            '<input type="text" ng-model="gridOptions.filterOptions.filterText" placeholder="Search..." class="search" style="width: 400px"/>' +
            '</div>' +
            '<div class="span3" style="text-align: right">' +
            '<i class="icon-plus-sign"></i> ' +
            '<gims-link-new origin="survey" target="questionnaire" return-tab="2"/>' +
            '</div>' +
            '</div>' +
            '<div ng-grid="gridOptions" class="gridStyle"></div>' +
            '</div>',
        // The linking function will add behavior to the template
        link: function () {
            // nothing to do ?
        },
        controller: function ($scope, $location, $resource, $routeParams, Restangular, Modal) {

            // Edit a questionnaire
            $scope.edit = function (row) {
                var returnUrl = $location.path();
                $location.path('/admin/questionnaire/edit/' + row.entity.id)
                    .search({
                        'returnUrl': returnUrl,
                        'returnTab': 2
                    })
                    .hash(null);
            };

            // Delete a questionnaire
            $scope.removeQuestionnaire = function (row) {
                var Questionnaire = new $resource('/api/questionnaire'); // TODO: find out a way to it with restangular instead of $resource
                var questionnaire = new Questionnaire(row.entity);
                Modal.confirmDelete(questionnaire, {objects: $scope.survey.questionnaires, label: questionnaire.name, returnUrl: $location.path()});
            };

            // Keep track of the selected row.
            $scope.selectedRow = [];

            // Configure ng-grid.
            $scope.gridOptions = {
                plugins: [new ngGridFlexibleHeightPlugin({minHeight: 100})],
                data: 'questionnaires',
                enableCellSelection: true,
                showFooter: true,
                selectedItems: $scope.selectedRow,
                filterOptions: {},
                multiSelect: false,
                columnDefs: [
                    {field: 'spatial', displayName: 'Spatial'},
                    {field: 'reporterNames', displayName: 'Filled by'},
                    {field: 'dateLastAnswerModification', displayName: 'Modif.', cellFilter: 'date:"dd MMM yyyy"'},
                    {field: 'validatorNames', displayName: 'Validation by'},
                    {field: 'completed', displayName: 'Completed', cellTemplate: '<div class="progress" style="margin: 5px 5px 0 5px">' +
                        '<div class="bar" ng-style="{width: row.entity[col.field] * 100}"></div>' +
                        '</div>'},
                    {displayName: '', cellTemplate: '<div style="margin: 5px 5px 0 5px ">' +
                        '<i class="icon-bullhorn icon-grid" ng-visible="row.entity.comments" data-toggle="tooltip" title="{{row.entity.comments}}"></i>' +
                        '<i class="icon-grid" ng-class="{\'icon-lock\': row.entity.permission.isLocked, \'icon-unlock\': !row.entity.permission.isLocked}"></i>' +
                        '<button type="button" class="btn btn-mini btn-edit" ng-click="edit(row)" ><i class="icon-pencil icon-large"></i></button>' +
                        '<button type="button" class="btn btn-mini btn-remove" ng-click="removeQuestionnaire(row)" ><i class="icon-trash icon-large"></i></button>' +
                        // ng-visible="row.entity.permission.canBeDeleted" @todo add me back to line above when permission are implemented
                        '</div>'}
                ]
            };

            Restangular
                .one('survey', $routeParams.id)
                .all('questionnaire')
                .getList({fields: 'dateLastAnswerModification,reporterNames,validatorNames,completed,spatial,comments'})
                .then(function (questionnaires) {
                    $scope.questionnaires = questionnaires;
                });
        }
    };
});