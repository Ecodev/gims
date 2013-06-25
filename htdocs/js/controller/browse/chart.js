angular.module('myApp').controller('Browse/ChartCtrl', function ($scope, $location, $http, $timeout, Restangular) {
    'use strict';

    $scope.chartObj;
    var fromUrl = $location.search()['exclude'];
    $scope.exclude = fromUrl ? fromUrl.split(',') : new Array(); // this triggers watch(scope.exclude) on page load

    // Whenever one of the parameter is changed
    var uniqueAjaxRequest;
    $scope.$watch('country.id + part.id + filterSet.id', function (a) {

        $scope.pointSelected = null;
        $scope.exclude = [];

        // If they are all available ...
        if ($scope.country && $scope.part && $scope.filterSet) {
            $scope.refreshChart();
        }
    });

    $scope.filters = [];
    $scope.columnDefs = [];

    $scope.selectedFilters = [];

    // Configure ng-grid.
    $scope.gridOptions = {
        plugins: [new ngGridFlexibleHeightPlugin({minHeight: 300})],
        data: 'filters',
        enableCellSelection: true,
        multiSelect: true,
        showSelectionCheckbox: true,
        selectWithCheckboxOnly: true,
        columnDefs: 'columnDefs',
        checkboxHeaderTemplate: '',
        selectedItems: $scope.selectedFilters
    };

    $scope.pointSelected = null;

    var excludedFilters = [];
    var lastSelectedPoint = null;

    var resetChart = function () {
        // hardcoded value for now.
        // By change there are two filter per filter-set. Each filter has two output... 2*2=4
        var index = 4;
        while($scope.chartObj.series[index]) {
            $scope.chartObj.series[index].remove();
        }
    };

    $scope.updateChartInProcess = false;

    $scope.updateChart = function() {

        resetChart();

        excludedFilters = [];
        // Find the not selected filters
        $('.gridStyle .ngSelectionCheckbox').each(function (index, element) {
            if ($(element).is(':checked') === false && $(element).is(':visible')) {
                excludedFilters.push($scope.filters[index].filter.id);
            }
        });

        refreshChartPartial();
    };

    // Whenever the list of excluded values is changed
    $scope.$watch('pointSelected', function (a) {
        if ($scope.pointSelected) {

            var questionnaireName = $scope.pointSelected.name;

            $scope.columnDefs = [];

            var cellTemplateFilter = '<div class="ngCellText" ng-class="col.colIndex()">' +
                '<span style="padding-left: {{row.entity.filter.level}}em;">{{row.entity.filter.name}}</span>' +
                '</div>';
            $scope.columnDefs.push({field: 'filter', displayName: 'Filter', width: 240, cellTemplate: cellTemplateFilter});
            $scope.columnDefs.push({field: 'values[0].' + $scope.part.name, displayName: questionnaireName, cellFilter: 'percent'});

            var parameters = {
                questionnaire: $scope.pointSelected.questionnaire,
                filter: $scope.pointSelected.filter,
                part: $scope.part.id
            };

            $scope.filters = Restangular.all('chartFilter').getList(parameters).then(function (data) {

                $scope.filters = data;

                $timeout(function () {
                    $('.gridStyle .ngSelectionHeader').hide();
                    $('.gridStyle .ngSelectionCheckbox').each(function (index, element) {
                        if ($scope.filters[index].selectable === false) {
                            $(element).hide();
                        } else {
                            $(element).click();
                        }
                    });
                }, 0);
            });
        }
    });

    // Whenever the list of excluded values is changed
    var refreshChartPartial = function() {

        if ($scope.exclude instanceof Array && typeof($scope.chartObj) !== 'undefined') {
            $scope.updateChartInProcess = true;
            var excludedSeries = new Array();
            for (var i = 0; i < $scope.exclude.length; i++) {
                var serie = $scope.exclude[i].split(':')[0];
                if (excludedSeries.indexOf(serie) === -1) {
                    excludedSeries.push(serie);
                }
            }
            var changed = false;
            for (var i = 0; i < $scope.chartObj.series.length; i++) {
                if ($scope.chartObj.series[i].name.indexOf('ignored answers') !== -1) {
                    $scope.chartObj.series[i].destroy(false);
                    changed = true;
                }
            }

            if ($scope.exclude.length > 0) {
                $http.get('/api/chart',
                    {
                        params: {
                            country: $scope.country.id,
                            part: $scope.part.id,
                            filterSet: $scope.filterSet.id,
                            exclude: $scope.exclude.join(','),
                            questionnaire: $scope.pointSelected.questionnaire,
                            excludedFilters: excludedFilters.join(','),
                            onlyExcluded: 1
                        }

                    }).success(function (data) {
                        if (typeof($scope.chartObj) !== 'undefined') {
                            // add/update from the chart line series with excluded values
                            for (var i = 0; i < data.length; i++) {
                                // refresh the serie data on the graph
                                $scope.chartObj.addSeries(data[i], false, false);
                            }
                            $scope.chartObj.redraw();
                            $scope.updateChartInProcess = false;
                        }
                    });
            } else if (changed) {
                // force chart refresh when no more answers are excluded
                $scope.chartObj.redraw();
            }
        }
    };

    $scope.refreshChart = function () {
        $scope.isLoading = true;
        $scope.plotColors = [];
        $timeout.cancel(uniqueAjaxRequest);
        uniqueAjaxRequest = $timeout(function () {
            // get chart data via Ajax, but only once per 200 milliseconds
            // (this avoid sending several request on page loading)
            $http.get('/api/chart',
                {
                    params: {
                        country: $scope.country.id,
                        part: $scope.part.id,
                        filterSet: $scope.filterSet.id,
                        exclude: $scope.exclude.join(','),
                        excludeFilter: excludedFilters.join(',')
                    }
                }).success(function (data) {

                    data.plotOptions.scatter.dataLabels.formatter = function () {
                        return $('<span/>').css({
                            'color': this.point.selected ? '#DDD' : this.series.color
                        }).text(this.point.name)[0].outerHTML;
                    }

                    data.plotOptions.scatter.point = {events: {
                        click: function (e) {
                            var idPart = e.currentTarget.id.split(':');
                            $scope.pointSelected = {
                                questionnaire: e.currentTarget.questionnaire,
                                name: e.currentTarget.name,
                                filter: idPart[0]
                            };
                            var questionnaire = e.currentTarget.id;
                            //var point = $scope.chartObj.get(e.currentTarget.id);
                            //point.select(null, true); // toggle point selection

                            $scope.exclude = [];
                            if (_.indexOf($scope.exclude, questionnaire) !== -1) {
                                $scope.exclude = _.without($scope.exclude, questionnaire);
                            }
                            else {
                                $scope.exclude.push(questionnaire);
                            }
                            //$location.search('exclude', $scope.exclude.join(','));
                            $scope.$apply(); // this is needed because we are outside the AngularJS context (highcharts uses jQuery event handlers)
                        }
                    }
                    };

                    $scope.chart = data;
                    $scope.isLoading = false;

                });

        }, 200);
    };
});
