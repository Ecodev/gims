/* Directives */
angular.module('myApp.directives').directive('gimsTable', function () {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with attribute
        // This HTML will replace the directive.
        replace: true,
        transclude: true,
        template: '<div class="container">'+
        '<form>'+
        '<div class="row">'+
        '    <span class="span6">'+
        '        <p><input name="questionnaire" ui-select2="select2.questionnaire.list" ng-model="select2.questionnaire.selected" data-placeholder="Select a questionnaire"  style="width:100%;"/></p>'+
        '        </span>'+
        '       </div>'+
        '       <div class="row">'+
        '       <span class="span6">'+
        '       <p><input name="filterSet" ui-select2="select2.filterSet.list" ng-model="select2.filterSet.selected" data-placeholder="Select a filter"  style="width:100%;"/></p>'+
        '       </span>'+
        '       <span class="span2">'+
        '       <p>&nbsp;<i class="icon-loading" ng-show="isLoading"></i></p>'+
        '       </span>'+
        '       </div>'+
        '       <div class="row">'+
        '       <div class="span6">'+
        '       <label class="checkbox">'+
        '       <input type="checkbox" ng-model="showOnlyTopLevel" ng-click="refresh()" /> Show only top levels'+
        '       </label>'+
        '       </div>'+
        '       </div>'+
        '       </form>'+
        '       <div class="alert alert-info" ng-hide="table"><i class="icon-info-sign"></i>Select parameters to show table here.</div>'+
        '       <div ng-grid="gridOptions" class="gridStyle"></div>'+
        '       </div>',
        // The linking function will add behavior to the template
        link: function () {
            // nothing to do ?
        },
        controller: function ($scope, $http, Select2Configurator, $timeout) {

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
        }
    };
});