'use strict';

/* Controllers */
angular.module('myApp').controller('AdminCtrl', function () {

});

angular.module('myApp').controller('Admin/Survey/EditCtrl', function ($scope, $routeParams, $location, Survey) {

});

angular.module('myApp').controller('Admin/SurveyCtrl', function ($scope, $routeParams, $location, $window, $timeout, Survey) {

    // Initialize
    $scope.filteringText = '';

    $scope.filterOptions = {
        filterText: 'filteringText',
        useExternalFilter: false
    };

    $scope.surveys = Survey.query(function(data) {

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
            {field: 'id', displayName: 'Id'},
            {field: 'name', displayName: 'Name'},
            {field: 'active', displayName: 'Active'},
            {field: 'year', displayName: 'Year'},
            {displayName: '', cellTemplate: '<button type="button" class="btn btn-mini" ng-click="edit(row)" ><i class="icon-pencil"></i></button>'}
        ]
    };

    $scope.edit = function edit(row) {
        $location.path('/admin/survey/edit/' + row.entity.id);
    }

    $scope.$on('filterChanged', function (evt, text) {
        $scope.filteringText = text;
    });
});