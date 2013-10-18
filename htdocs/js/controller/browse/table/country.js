
angular.module('myApp').controller('Browse/Table/CountryCtrl', function($scope, $http, $timeout, $location) {
    'use strict';

    // Init to empty
    $scope.columnDefs = [];
    $scope.years = $location.search().years;


    // Configure ng-grid.
    $scope.gridOptions = {
        data: 'table',
        plugins: [new ngGridFlexibleHeightPlugin({minHeight: 400})],
        columnDefs: 'columnDefs'
    };


    $scope.updateUrl = function()
    {
        if($scope.years==''){
            $location.search('years', null);
        } else {
            $location.search('years', $scope.years);
        }
    }


//    $scope.$watch('country + filterSet.id + years' , function(a) {
//        $scope.displayTable();
//    });

    // Whenever one of the parameter is changed
    var uniqueAjaxRequest;
    $scope.displayTable = function() {

        // If they are all available ...
        if ($scope.country && $scope.filterSet && $scope.years && $scope.years.length>=4) {

            // Build export URL
            var filterSetName = $scope.filterSet ? ' - ' + $scope.filterSet.name : '';
            var filename = _.pluck($scope.country, 'iso3').join(', ') + filterSetName + '.xlsx';
            $scope.exportUrl = $location.url().replace('browse/table/country', 'api/table/country/' + filename);

            $scope.isLoading = true;

            uniqueAjaxRequest = $timeout(function() {

                // ... then, get table data via Ajax, but only once per 200 milliseconds
                // (this avoid sending several request on page loading)
                var params = $location.search();
                params.years = $scope.years;
                $http.get('/api/table/country', {params:params}).success(function(data) {
                    $scope.table = data.data;

                    $scope.columnDefs = _.map(data.columns, function(columnName, columnKey) {
                        return {field: columnKey, displayName: columnName, width:'100px'};
                    });
                    $scope.isLoading = false;
                });
            }, 200);
        }
    }
});
