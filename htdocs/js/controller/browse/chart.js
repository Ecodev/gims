
angular.module('myApp').controller('Browse/ChartCtrl', function ($scope, $location, $http, $timeout) {
    'use strict';

    // Whenever one of the parameter is changed
    var uniqueAjaxRequest;
    $scope.$watch('country.id + part.id + filterSet.id + exclude', function (a) {

        // If they are all available ...
        if ($scope.country && $scope.part && $scope.filterSet) {
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
                            country: $scope.country.id,
                            part: $scope.part.id,
                            filterSet: $scope.filterSet.id,
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
