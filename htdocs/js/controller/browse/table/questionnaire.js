
angular.module('myApp').controller('Browse/Table/QuestionnaireCtrl', function($scope, $http, $timeout, $location) {
    'use strict';

    // Init to empty
    $scope.columnDefs = [];

    // Configure ng-grid.
    $scope.gridOptions = {
        data: 'table',
        plugins: [new ngGridFlexibleHeightPlugin({minHeight: 400})],
        columnDefs: 'columnDefs'
    };

    // Whenever one of the parameter is changed
    var uniqueAjaxRequest;
    $scope.$watch('country + filterSet.id', function(a) {

        // If they are all available ...
        if ($scope.country && $scope.filterSet) {

            // Build export URL
            var filterSetName = $scope.filterSet ? ' - ' + $scope.filterSet.name : '';
            var filename = _.pluck($scope.country, 'iso3').join(', ') + filterSetName + '.csv';
            $scope.exportUrl = $location.url().replace('browse/table/questionnaire', 'api/table/questionnaire/' + filename);

            $scope.isLoading = true;

            uniqueAjaxRequest = $timeout(function() {

                // ... then, get table data via Ajax, but only once per 200 milliseconds
                // (this avoid sending several request on page loading)
                $http.get('/api/table/questionnaire',
                        {
                            params: $location.search()
                        }).success(function(data) {
                    $scope.table = data.data;

                    $scope.columnDefs = _.map(data.columns, function(columnName, columnKey) {
                        return {field: columnKey, displayName: columnName};
                    });
                    $scope.isLoading = false;
                });
            }, 200);
        }
    });
});
