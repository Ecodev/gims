
angular.module('myApp').controller('Browse/Table/FilterCtrl', function($scope, $http, $timeout) {
    'use strict';

    $scope.showOnlyTopLevel = true;

    var columnDefTemplateBase = {sortable: false, field: 'filter.name', displayName: 'Filter', cellTemplate: '<div class="ngCellText" ng-class="col.colIndex()"><span style="padding-left: {{row.entity.filter.level}}em;">{{row.entity.filter.name}}</span></div>'};
    var parts = ['Urban', 'Rural', 'Total'];

    // Init to empty
    $scope.columnDefs = [];

    // Configure ng-grid.
    $scope.gridOptions = {
        data: 'table',
        enableColumnResize: true,
        plugins: [new ngGridFlexibleHeightPlugin({minHeight: 400})],
        columnDefs: 'columnDefs'
    };

    var originalTable;
    var refresh = function() {
        var result = [];
        angular.forEach(originalTable, function(e) {
            if (!$scope.showOnlyTopLevel || !e.filter.level) {
                result.push(e);
            }
        });

        $scope.table = result;
    };

    $scope.$watch('showOnlyTopLevel', refresh);

    // Whenever one of the parameter is changed
    var uniqueAjaxRequest;
    $scope.$watch('questionnaire + filterSet.id', function() {

        var parameters = {};
        var questionnaires = [];

        // One questionnaire is selected
        if ($scope.questionnaire && $scope.questionnaire.id) {
            questionnaires.push($scope.questionnaire);
        } else {
            angular.forEach($scope.questionnaire, function(questionnaire) {
                if (questionnaire.id) {
                    questionnaires.push(questionnaire);
                }
            });
        }

        // build parameters
        var ids = [];
        var index;

        for (index in questionnaires) {
            ids.push(questionnaires[index].id);
        }
        parameters.questionnaire = ids.join(',');

        // If they are all available ...
        if (parameters.questionnaire && $scope.filterSet) {

            // Update column defs with base first
            var columnDefs = [];
            columnDefs.push(columnDefTemplateBase);

            // build
            for (index in questionnaires) {

                var questionnaireName = '';
                var regexp = /[\w]+/ig;
                var result = regexp.exec(questionnaires[index].name);
                if (typeof result === 'object' && typeof result[0] === 'string') {
                    questionnaireName = result[0];
                }

                // retrieve the questionnaire name.
                for (var index2 in parts) {
                    var partName = parts[index2];
                    columnDefs.push({
                        width: '7%',
                        sortable: false,
                        field: 'values[' + index + '].' + partName,
                        displayName: partName.substr(0, 1) + ' ' + questionnaireName
                    });
                }
            }
            $scope.columnDefs = columnDefs;

            $scope.isLoading = true;

            uniqueAjaxRequest = $timeout(function() {

                // ... then, get table data via Ajax, but only once per 200 milliseconds
                // (this avoid sending several request on page loading)
                $http.get('/api/table/filter', {
                    params: {
                        questionnaire: parameters.questionnaire,
                        filterSet: $scope.filterSet.id
                    }
                }).success(function(data) {
                    originalTable = data;
                    refresh();
                    $scope.isLoading = false;
                });
            }, 200);
        }
    });
});
