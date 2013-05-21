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
            '<input type="text" ng-model="filteringText" placeholder="Search..." class="search" style="width: 400px"/>' +
            '</div>' +
            '<div class="span3" style="text-align: right">' +
            '<i class="icon-plus-sign"></i> ' +
            '<gims-link-new origin="survey" target="questionnaire"/>' +
            '</div>' +
            '</div>' +
            '<div ng-grid="gridOptions" class="gridStyle"></div>' +
            '</div>',
        // The linking function will add behavior to the template
        link: function () {
            // nothing to do ?
        },
        controller: function ($scope, $location, $resource, Restangular, Modal) {

            // Edit a questionnaire
            $scope.edit = function (row) {
                var currentUrl = $location.path();
                $location.path('/admin/questionnaire/edit/' + row.entity.id).search({'returnUrl': currentUrl});
            };

            // Delete a questionnaire
            $scope.removeQuestionnaire = function (row) {
                var Questionnaire = new $resource('/api/questionnaire'); // TODO: find out a way to it with restangular instead of $resource
                var questionnaire = new Questionnaire(row.entity);
                Modal.confirmDelete(questionnaire, {objects: $scope.survey.questionnaires, label: questionnaire.name, returnUrl: $location.path()});
            };

            // Keep track of the selected row.
            $scope.selectedRow = [];

            // Initialize
            $scope.filteringText = '';
            $scope.filterOptions = {
                filterText: 'filteringText',
                useExternalFilter: false
            };

            // Configure ng-grid.
            $scope.gridOptions = {
                plugins: [new ngGridFlexibleHeightPlugin({minHeight: 100})],
                data: 'survey.questionnaires',
                enableCellSelection: true,
                showFooter: true,
                selectedItems: $scope.selectedRow,
                filterOptions: $scope.filterOptions,
                multiSelect: false,
                columnDefs: [
                    {field: 'name', displayName: 'Name', width: '500px'},
                    {displayName: '', cellTemplate: '<button type="button" class="btn btn-mini btn-edit" ng-click="edit(row)" ><i class="icon-pencil icon-large"></i></button>' +
                        '<button type="button" class="btn btn-mini btn-remove" ng-click="removeQuestionnaire(row)" ><i class="icon-trash icon-large"></i></button>'}
                ]
            };

            $scope.$on('filterChanged', function (evt, text) {
                console.log(text);
                $scope.filteringText = text;
            });
        }
    };
});