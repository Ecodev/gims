angular.module('myApp').controller('Browse/Table/QuestionnaireCtrl', function($scope, $http, $timeout, $location, Restangular) {
    'use strict';

    // Init to empty
    $scope.tabs = {};
    $scope.columnDefs = [];
    $scope.surveysTemplate = "[[item.code]] - [[item.name]]";

    // Configure ng-grid.
    $scope.gridOptions = {
        data: 'table',
        plugins: [new ngGridFlexibleHeightPlugin({minHeight: 400})],
        columnDefs: 'columnDefs'
    };

    $scope.$watch('tabs.filterSets', function() {
        if ($scope.tabs.filterSets) {
            $scope.tabs.filters = [];
            Restangular.one('filterSet').get({id: _.pluck($scope.tabs.filterSets, 'id').join(','), fields: 'filters', perPage: 1000}).then(function(data) {
                var filters = [];
                if (_.isArray(data)) {
                    angular.forEach(data, function(obj) {
                        filters = filters.concat(obj.filters);
                    });
                } else {
                    filters = data.filters;
                }
                $scope.tabs.filters = filters;
                $scope.tabs.filter = null;
            });
        }
    });

    $scope.$watch('tabs.filter', function() {
        if ($scope.tabs.filter) {
            Restangular.one('filter', $scope.tabs.filter.id).getList('children', {perPage: 1000}).then(function(filters) {
                $scope.tabs.filters = filters;
                $scope.tabs.filterSets = null;
            });
        }
    });

    $scope.$watch('tabs.geonames', function() {
        if ($scope.tabs.geonames) {
            $scope.tabs.questionnaires = [];
            Restangular.one('geoname').get({id: _.pluck($scope.tabs.geonames, 'id').join(','), fields: 'questionnaires.permissions', perPage: 1000}).then(function(data) {
                var questionnaires = [];
                if (_.isArray(data)) {
                    angular.forEach(data, function(obj) {
                        questionnaires = questionnaires.concat(obj.questionnaires);
                    });
                } else {
                    questionnaires = data.questionnaires;
                }
                $scope.tabs.questionnaires = _.filter(questionnaires, function(q) {
                    if (q.permissions.read) {
                        return true;
                    }
                });
                $scope.tabs.surveys = null;
            });
        }
    });

    $scope.$watch('tabs.surveys', function() {
        if ($scope.tabs.surveys) {
            $scope.tabs.questionnaires = [];
            Restangular.one('survey').get({id: _.pluck($scope.tabs.surveys, 'id').join(','), fields: 'questionnaires.permissions', perPage: 1000}).then(function(data) {
                var questionnaires = [];
                if (_.isArray(data)) {
                    angular.forEach(data, function(obj) {
                        questionnaires = questionnaires.concat(obj.questionnaires);
                    });
                } else {
                    questionnaires = data.questionnaires;
                }
                $scope.tabs.questionnaires = _.filter(questionnaires, function(q) {
                    if (q.permissions.read) {
                        return true;
                    }
                });
                $scope.tabs.geonames = null;
            });
        }
    });

    $scope.$watch('tabs.filters', function() {
        $scope.filtersIds = _.pluck($scope.tabs.filters, 'id').join(',');
        refresh();
    });

    $scope.$watch('tabs.questionnaires', function() {
        $scope.questionnairesIds = _.pluck($scope.tabs.questionnaires, 'id').join(',');
        refresh();
    });

    var refresh = _.debounce(function() {
        if ($scope.tabs.questionnaires && $scope.tabs.filters) {

            $http.get('/api/table/questionnaire', {
                params: {
                    questionnaires: $scope.questionnairesIds,
                    filters: $scope.filtersIds
                }
            }).success(function(data) {
                $scope.table = data.data;

                $scope.columnDefs = _.map(data.columns, function(columnName, columnKey) {
                    return {field: columnKey, displayName: columnName, width: '100px'};
                });
                $scope.legends = data.legends;
                $scope.isLoading = false;
            });
        }
    }, 300);

});
