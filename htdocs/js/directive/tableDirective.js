/* Directives */
angular.module('myApp.directives').directive('gimsTable', function () {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with attribute
        // This HTML will replace the directive.
        replace: true,
        transclude: true,
        template: '<div class="container">' +
            '<form>' +
            '<div class="row">' +
            '    <span class="span6">' +
            '        <p><gims-select api="questionnaire" multiple model="questionnaire" placeholder="Select a questionnaire" style="width:100%;"></gims-select></p>' +
            '        </span>' +
            '       </div>' +
            '       <div class="row">' +
            '       <span class="span6">' +
            '       <p><gims-select api="filterSet" model="filterSet" placeholder="Select a filter" style="width:100%;"></gims-select></p>' +
            '       </span>' +
            '       <span class="span2">' +
            '       <p>&nbsp;<i class="icon-loading" ng-show="isLoading"></i></p>' +
            '       </span>' +
            '       </div>' +
            '       <div class="row">' +
            '       <div class="span6">' +
            '       <label class="checkbox">' +
            '       <input type="checkbox" ng-model="showOnlyTopLevel" ng-click="refresh()" /> Show only top levels' +
            '       </label>' +
            '       </div>' +
            '       </div>' +
            '       </form>' +
            '       <div class="alert alert-info" ng-hide="table"><i class="icon-info-sign"></i> Select parameters to show table here.</div>' +
            '       <div ng-grid="gridOptions" class="gridStyle"></div>' +
            '       </div>',
        // The linking function will add behavior to the template
        link: function () {
            // nothing to do ?
        },
        controller: function ($scope, $http, $timeout, $location) {
            $scope.showOnlyTopLevel = true;

            var columnDefTemplateBase = {sortable: false, field: 'filter.name', displayName: 'Filter', cellTemplate: '<div class="ngCellText" ng-class="col.colIndex()"><span style="padding-left: {{row.entity.filter.level}}em;">{{row.entity.filter.name}}</span></div>'};
            var parts = ['Urban', 'Rural', 'Total'];

            // Init to empty
            $scope.columnDefs = [];

            // Configure ng-grid.
            $scope.gridOptions = {
                data: 'table',
                plugins: [new ngGridFlexibleHeightPlugin({minHeight: 400})],
                columnDefs: 'columnDefs'
            };

            var originalTable;
            $scope.refresh = function () {
                var result = [];
                angular.forEach(originalTable, function (e) {
                    if (!$scope.showOnlyTopLevel || !e.filter.level) {
                        result.push(e);
                    }
                });

                $scope.table = result;
            };


            // Whenever one of the parameter is changed
            var uniqueAjaxRequest;
            $scope.$watch('questionnaire + filterSet.id', function (a) {

                var parameters = {};
                var questionnaires = [];

                // One questionnaire is selected
                if ($scope.questionnaire && $scope.questionnaire.id) {
                    questionnaires.push($scope.questionnaire);
                } else {
                    angular.forEach($scope.questionnaire, function (questionnaire) {
                        if (questionnaire.id) {
                            questionnaires.push(questionnaire);
                        }
                    });
                }

                // build parameters
                var ids = [];
                for (var index in questionnaires) {
                    ids.push(questionnaires[index].id);
                }
                parameters.questionnaire = ids.join(',');

                // If they are all available ...
                if (parameters.questionnaire && $scope.filterSet) {

                    // Update column defs with base first
                    var columnDefs = [];
                    columnDefs.push(columnDefTemplateBase);

                    // build
                    for (var index in questionnaires) {

                        var questionnaireName = '';
                        var regexp = /[\w]+/ig;
                        var result = regexp.exec(questionnaires[index].name);
                        if (typeof result === 'object' && typeof result[0] === 'string') {
                            questionnaireName = result[0];
                        }

                        // retrieve the questionnaire name.
                        for (var index2 in parts) {
                            var partName = parts[index2];
                            columnDefs.push({sortable: false, field: 'values[' + index + '].' + partName, displayName: partName + ' - ' + questionnaireName, cellFilter: 'percent'});
                        }
                    }
                    $scope.columnDefs = columnDefs;

                    $scope.isLoading = true;

                    uniqueAjaxRequest = $timeout(function () {

                        // ... then, get table data via Ajax, but only once per 200 milliseconds
                        // (this avoid sending several request on page loading)
                        $http.get('/api/table',
                            {
                                params: {
                                    questionnaire: parameters.questionnaire,
                                    filterSet: $scope.filterSet.id
                                }
                            }).success(function (data) {
                                originalTable = data;
                                $scope.refresh();
                                $scope.isLoading = false;
                            });
                    }, 200);
                }
            });
        }
    };
});