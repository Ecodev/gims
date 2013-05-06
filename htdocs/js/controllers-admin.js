'use strict';

/* Controllers */
angular.module('myApp').controller('AdminCtrl', function () {

});

angular.module('myApp').controller('Admin/Survey/CrudCtrl', function ($scope, $routeParams, $location, $resource, Survey, ConfirmDelete) {

    $scope.actives = [
        {text: 'Yes', value: 'true'},
        {text: 'No', value: 'false'}
    ];

    // Defining label for GUI.
    $scope.sending = false;
    $scope.sendLabel = 'Save';

    $scope.save = function () {
        $scope.sending = true;
        $scope.sendLabel = 'Saving...';

        // First case is for update a survey, second is for creating
        if ($scope.survey.id > 0) {
            $scope.survey.$update({id: $scope.survey.id}, function (survey) {
                $location.path('/admin/survey');
            });
        } else {
            $scope.survey = new Survey($scope.survey);
            $scope.survey.$create(function (survey) {
                $location.path('/admin/survey');
            });
        }
    };

    // Delete a survey
    $scope.delete = function () {
        ConfirmDelete.show($scope.survey);
    };

    // Load survey if possible
    if ($routeParams.id > 0) {
        $resource('/api/survey/:id?fields=metadata').get({id: $routeParams.id}, function (survey) {

            // cast a few variable
            survey.year -= 0; // int value
            survey.active += ''; // string value
            $scope.survey = new Survey(survey);
        });
    }
});

/**
 * Admin Survey Controller
 */
angular.module('myApp').controller('Admin/SurveyCtrl', function ($scope, $routeParams, $location, $window, $timeout, Survey, ConfirmDelete) {

    // Initialize
    $scope.filteringText = '';

    $scope.filterOptions = {
        filterText: 'filteringText',
        useExternalFilter: false
    };

    $scope.surveys = Survey.query(function (data) {

        // Trigger resize event informing elements to resize according to the height of the window.
        $timeout(function () {
            angular.element($window).resize();
        }, 0)
    });

    // Keep track of the selected row.
    $scope.selectedRow = [];

    // Configure ng-grid.
    $scope.gridOptions = {
        data: 'surveys',
        enableCellSelection: true,
        showFooter: true,
        selectedItems: $scope.selectedRow,
        filterOptions: $scope.filterOptions,
        multiSelect: false,
        columnDefs: [
            {field: 'code', displayName: 'Code', width: '150px'},
            {field: 'name', displayName: 'Name', width: '750px'},
            {field: 'active', displayName: 'Active', cellFilter: 'checkmark', width: '100px'},
            {field: 'year', displayName: 'Year', width: '100px'},
            {displayName: '', cellTemplate: '<button type="button" class="btn btn-mini" ng-click="edit(row)" ><i class="icon-pencil icon-large"></i></button>' +
                '<button type="button" class="btn btn-mini" ng-click="delete(row)" ><i class="icon-trash icon-large"></i></button>'}
        ]
    };

    $scope.delete = function (row) {

        // Add a little timeout to enabling the event "selectRow" to be propagated
        $timeout(function () {
            ConfirmDelete.show($scope.selectedRow[0], $scope.surveys);
        }, 0)
    }

    $scope.edit = function (row) {
        $location.path('/admin/survey/edit/' + row.entity.id);
    }

    $scope.$on('filterChanged', function (evt, text) {
        $scope.filteringText = text;
    });
});