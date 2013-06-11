
angular.module('myApp').controller('Browse/ChartCtrl', function ($scope, $location, $http, Select2Configurator, $timeout) {
    'use strict';

    // Configure select2 via our helper service
    Select2Configurator.configure($scope, 'country');
    Select2Configurator.configure($scope, 'part');
    Select2Configurator.configure($scope, 'filterSet');

    // Whenever one of the parameter is changed
    var uniqueAjaxRequest;
    $scope.$watch('select2.country.selected.id + select2.part.selected.id + select2.filterSet.selected.id + exclude', function (a) {

        // If they are all available ...
        if ($scope.select2.country.selected && $scope.select2.part.selected && $scope.select2.filterSet.selected) {
            $scope.refreshChart();
        }
    });

    $scope.refreshChart = function() {
        $scope.isLoading = true;
        $scope.plotColors = [];
        $timeout.cancel(uniqueAjaxRequest);
        uniqueAjaxRequest = $timeout(function () {

            // ... then, get chart data via Ajax, but only once per 200 milliseconds
            // (this avoid sending several request on page loading)
            $http.get('/api/chart',
                    {
                        params: {
                            country: $scope.select2.country.selected.id,
                            part: $scope.select2.part.selected.id,
                            filterSet: $scope.select2.filterSet.selected.id,
                            exclude: $scope.exclude,
                            refresh: 1
                        }
                    }).success(function (data) {

                    data.plotOptions.scatter.point = {events:
                        {
                            click: function(e) {
                                var excluded = [];
                                var fromUrl = $location.search()['exclude'];
                                if (typeof (fromUrl) != 'undefined')
                                {
                                    excluded = _.without(fromUrl.split(','), '');
                                }
                                var questionnaire = e.currentTarget.id;
                                if (_.indexOf(excluded, questionnaire) != -1)
                                {
                                    excluded = _.without(excluded, questionnaire);
                                }
                                else
                                {
                                    excluded.push(questionnaire);
                                }
                                $scope.exclude = excluded.join(',');
                                $location.search('exclude', $scope.exclude);
                                $scope.$apply(); // this is needed because we are outside the AngularJS context (highcharts uses jQuery event handlers)
                            }
                        }
                    }

                $scope.chart = data;
                $scope.isLoading = false;
            });
        }, 200);
    };
});
