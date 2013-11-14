angular.module('myApp').controller('Browse/ChartCtrl', function($scope, $location, $http, $timeout, Restangular, $modal) {
    'use strict';

    $scope.chartObj;
    var fromUrl = $location.search()['excludedQuestionnaires'];
    $scope.excludedQuestionnaires = fromUrl ? fromUrl.split(',') : []; // this triggers watch(scope.exclude) on page load

    $scope.$watch('excludedQuestionnaires', function() {

        if ($scope.excludedQuestionnaires && $scope.excludedQuestionnaires.length) {
            $location.search('excludedQuestionnaires', $scope.excludedQuestionnaires.join(','));
        }
    }, true);

    // Whenever one of the parameter is changed
    var uniqueAjaxRequest;
    $scope.$watch('country.id + part.id + filterSet.id', function(a) {

        $scope.pointSelected = null;

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

    $scope.getExcludedFilters = function() {
        var excludedFilters = [];

        // Find the not selected filters
        $('.gridStyle .ngSelectionCheckbox').each(function(index, element) {
            if ($(element).is(':checked') === false && $(element).is(':visible')) {
                excludedFilters.push($scope.pointSelected.filter + ':' + $scope.filters[index].filter.id);
            }
        });

        return excludedFilters;
    };

    $scope.ignoreQuestionnaire = function() {
        $scope.excludedQuestionnaires.push($scope.pointSelected.id);
        $scope.refreshChart();
    };

    $scope.openNewFilterSet = function() {
        $modal.open({
            templateUrl: 'newFilterSet.html',
            controller: 'Browse/ChartNewFilterSetCtrl',
            scope: $scope
        });
    };

    // Whenever the list of excluded values is changed
    $scope.$watch('pointSelected', function(a) {
        if ($scope.pointSelected) {

            // We throw an window.resize event to force Highcharts to reflow and adapt to its new size
            $timeout(function() {
                jQuery(window).resize();
            }, 1);

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
                filterSet: $scope.filterSet.id,
                part: $scope.part.id
            };

            $scope.isLoading = true;
            $scope.filters = Restangular.all('chartFilter').getList(parameters).then(function(data) {
                $scope.isLoading = false;
                $scope.filters = data;

                $timeout(function() {
                    $('.gridStyle .ngSelectionHeader')
                            .after('<input type="checkbox" class="ngSelectionHeader" checked="checked"/>') // add custom checkbox
                            .remove(); // remove default head checkbox

                    $('.gridStyle .ngSelectionCheckbox').each(function(index, element) {
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
    $('.gridStyle').delegate('.ngSelectionHeader', 'click', function() {
        var self = this;
        $('.gridStyle .ngSelectionCheckbox').each(function(index, element) {
            if ($scope.filters[index].selectable === true) {
                if ($(self).is(':checked') !== $(element).is(':checked')) {
                    $(element).click();
                }
            }
        });
    });

    $scope.refreshChart = function() {
        $scope.isLoading = true;
        $timeout.cancel(uniqueAjaxRequest);
        uniqueAjaxRequest = $timeout(function() {
            // get chart data via Ajax, but only once per 200 milliseconds
            // (this avoid sending several request on page loading)
            $http.get('/api/chart',
                    {
                        params: {
                            country: $scope.country.id,
                            part: $scope.part.id,
                            filterSet: $scope.filterSet.id,
                            excludedQuestionnaires: $scope.excludedQuestionnaires.join(','),
                            excludedFilters: $scope.getExcludedFilters().join(',')
                        }
                    }).success(function(data) {

                data.plotOptions.scatter.dataLabels.formatter = function() {
                    return $('<span/>').css({
                        color: this.point.selected ? '#DDD' : this.series.color
                    }).text(this.point.name)[0].outerHTML;
                };

                data.plotOptions.scatter.point = {events: {
                        click: function(e) {
                            var ids = e.currentTarget.id.split(':');
                            $scope.pointSelected = {
                                id: e.currentTarget.id,
                                questionnaire: e.currentTarget.questionnaire,
                                name: e.currentTarget.name,
                                filter: ids[0]
                            };
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


angular.module('myApp').controller('Browse/ChartNewFilterSetCtrl', function($scope, $location, $http, $timeout, $modalInstance) {
    'use strict';
    $scope.newFilterSet = {};
    $scope.cancelNewFilterSet = function() {
        $modalInstance.dismiss();
    };

    $scope.createNewFilterSet = function() {
        if ($scope.newFilterSet.name) {
            $http.post('/api/filterSet', {
                name: $scope.newFilterSet.name,
                originalFilterSet: $scope.filterSet.id,
                excludedFilters: $scope.getExcludedFilters()
            }).success(function(data) {
                $location.search('filterSet', data.id);

                // reload page
                $timeout(function() {
                    window.location.reload();
                }, 0);
            });
        }
    };
});