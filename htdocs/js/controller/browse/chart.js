
angular.module('myApp').controller('Browse/ChartCtrl', function ($scope, $http, Select2Configurator, $timeout) {
    'use strict';

    // Configure select2 via our helper service
    Select2Configurator.configure($scope, 'country');
    Select2Configurator.configure($scope, 'part');
    Select2Configurator.configure($scope, 'filterSet');

    // Whenever one of the parameter is changed
    var uniqueAjaxRequest;
    $scope.$watch('select2.country.selected.id + select2.part.selected.id + select2.filterSet.selected.id', function (a) {

        // If they are all available ...
        if ($scope.select2.country.selected && $scope.select2.part.selected && $scope.select2.filterSet.selected) {
            $scope.isLoading = true;
            $timeout.cancel(uniqueAjaxRequest);
            uniqueAjaxRequest = $timeout(function () {

                // ... then, get chart data via Ajax, but only once per 200 milliseconds
                // (this avoid sending several request on page loading)
                $http.get('/api/chart',
                        {
                            params: {
                                country: $scope.select2.country.selected.id,
                                part: $scope.select2.part.selected.id,
                                filterSet: $scope.select2.filterSet.selected.id
                            }
                        }).success(function (data) {
                    $scope.chart = data;
                    $scope.isLoading = false;
                });
            }, 200);
        }
    });

});
