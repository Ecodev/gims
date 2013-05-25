
angular.module('myApp').controller('Browse/TableCtrl', function($scope, $http, Select2Configurator, $timeout) {
    'use strict';

    // Configure select2 via our helper service
    Select2Configurator.configure($scope, 'questionnaire');
    Select2Configurator.configure($scope, 'filterSet');


    // Configure ng-grid.
    $scope.gridOptions = {
        data: 'table',
        plugins: [new ngGridFlexibleHeightPlugin({minHeight: 400})],
        columnDefs: [
            {sortable: false, field: 'filter.name', displayName: 'Filter', cellTemplate: '<div class="ngCellText" ng-class="col.colIndex()"><span style="padding-left: {{row.entity.filter.level}}em;">{{row.entity.filter.name}}</span></div>'},
            {sortable: false, field: 'values.Urban', displayName: 'Urban', cellFilter: 'percent'},
            {sortable: false, field: 'values.Rural', displayName: 'Rural', cellFilter: 'percent'},
            {sortable: false, field: 'values.Total', displayName: 'Total', cellFilter: 'percent'}
        ]
    };

    var originalTable;
    $scope.refresh = function() {

        var result = [];
        angular.forEach(originalTable, function(e) {
            if (!$scope.showOnlyTopLevel || !e.filter.level) {
                result.push(e);
            }
        });

        $scope.table = result;
    };

    // Whenever one of the parameter is changed
    var uniqueAjaxRequest;
    $scope.$watch('select2.questionnaire.selected.id + select2.filterSet.selected.id', function(a) {

        // If they are all available ...
        if ($scope.select2.questionnaire.selected && $scope.select2.filterSet.selected) {
            $scope.isLoading = true;
            $timeout.cancel(uniqueAjaxRequest);
            uniqueAjaxRequest = $timeout(function() {

                // ... then, get table data via Ajax, but only once per 200 milliseconds
                // (this avoid sending several request on page loading)
                $http.get('/api/table',
                        {
                            params: {
                                questionnaire: $scope.select2.questionnaire.selected.id,
                                filterSet: $scope.select2.filterSet.selected.id
                            }
                        }).success(function(data) {
                    originalTable = data;
                    $scope.refresh();
                    $scope.isLoading = false;
                });
            }, 200);
        }
    });

});
