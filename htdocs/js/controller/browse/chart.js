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
            refreshChart();
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

    var serieLength = null;

    var resetChart = function () {
        // hardcoded value for now.
        // By change there are two filter per filter-set. Each filter has two output... 2*2=4
        while ($scope.chartObj.series[serieLength]) {
            $scope.chartObj.series[serieLength].remove();
        }
    };

    var getExcludedFilters = function () {
        var excludedFilters = [];

        // Find the not selected filters
        $('.gridStyle .ngSelectionCheckbox').each(function (index, element) {
            if ($(element).is(':checked') === false && $(element).is(':visible')) {
                excludedFilters.push($scope.filters[index].filter.id);
            }
        });

        return excludedFilters;
    };

    /**
     * Return a parameter value
     *
     * $location.search('filterSet') does not work and don't have the time to search why....
     * Not enough in Angular context??
     *
     * @param parameterName
     * @returns {string}
     */
    var getParameterValue = function (parameterName) {
        // parameterName is not used for now. This function is anyway hacky...

        var result = '';
        var regexp = /filterSet=([0-9]+)/g;
        var searches = regexp.exec(window.location.search);

        if (searches[1] !== undefined) {
            result = searches[1];
        }
        return result;
    };


    $scope.ignoreQuestionnaire = function () {
        $scope.caseQuestionnaireExcluded = true;
        resetChart();
        refreshChartPartial();
    };

    $scope.updateChart = function () {
        $scope.caseQuestionnaireExcluded = false;
        resetChart();
        refreshChartPartial();
    };

    $scope.openNewFilterSet = function() {
        $scope.newFilterSetOpened = true;
    };

    $scope.closeNewFilterSet = function() {
        $scope.newFilterSetOpened = false;
    };

    $scope.createNewFilterSet = function() {
        if ($scope.newFilterSetName) {
            $http.post('/api/filterSet', {
                name: $scope.newFilterSetName,
                originalFilterSet: getParameterValue('filterSet'),
                excludedFilters: getExcludedFilters()
            }).success(function (data) {
                    $location.search('filterSet', data.id);

                    // reload page
                    $timeout(function () {
                        window.location.reload();
                    }, 0);
                });
        }
    };

    // Whenever the list of excluded values is changed
    $scope.$watch('pointSelected', function (a) {
        if ($scope.pointSelected) {

            // We throw an window.resize event to force Highcharts to reflow and adapt to its new size
            $timeout(function() { jQuery(window).resize(); }, 1);

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
                filterSet: getParameterValue('filterSet'),
                part: $scope.part.id
            };

            $scope.isLoading = true;
            $scope.filters = Restangular.all('chartFilter').getList(parameters).then(function (data) {
                $scope.isLoading = false;
                $scope.filters = data;

                $timeout(function () {
                    $('.gridStyle .ngSelectionHeader')
                        .after('<input type="checkbox" class="ngSelectionHeader" checked="checked"/>') // add custom checkbox
                        .remove(); // remove default head checkbox

                    $('.gridStyle .ngSelectionCheckbox').each(function (index, element) {
                        if ($scope.filters[index].selectable === false) {
                            $(element).hide();
                        } else {
                            if ($scope.filters[index].selected) {
                                $(element).click();
                            }
                        }
                    });
                }, 0);
            });
        }
    });

    /**
     * Add listener when toggling top checkbox
     */
    $('.gridStyle').delegate('.ngSelectionHeader', 'click', function () {
        var self = this;
        $('.gridStyle .ngSelectionCheckbox').each(function (index, element) {
            if ($scope.filters[index].selectable === true) {
                if ($(self).is(':checked') !== $(element).is(':checked')) {
                    $(element).click();
                }
            }
        });
    });

    // Whenever the list of excluded values is changed
    var refreshChartPartial = function () {

        if ($scope.exclude instanceof Array && typeof($scope.chartObj) !== 'undefined') {
            $scope.isLoading = true;
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
                            excludedFilters: getExcludedFilters().join(','),
                            caseQuestionnaireExcluded: $scope.caseQuestionnaireExcluded,
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
                            $scope.isLoading = false;
                        }
                    });
            } else if (changed) {
                // force chart refresh when no more answers are excluded
                $scope.chartObj.redraw();
            }
        }
    };

    var refreshChart = function () {
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
                        excludeFilter: getExcludedFilters().join(',')
                    }
                }).success(function (data) {

                    serieLength = data.series.length;

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
