angular.module('myApp').controller('Browse/Table/QuestionnaireCtrl', function($scope, $http, $timeout, $location, Restangular, Utility, TableAssistant) {
    'use strict';

    // Init to empty
    $scope.tabs = {};
    $scope.columnDefs = [];

    $scope.gridOptions = {
        data: 'table'
    };

    $scope.$watch('tabs.filterSets', function() {
        if ($scope.tabs.filterSets && $scope.tabs.filterSets.length) {
            $scope.tabs.filters = [];
            Restangular.one('filterSet').get({
                id: _.pluck($scope.tabs.filterSets, 'id').join(','),
                fields: 'filters.color',
                perPage: 1000
            }).then(function(data) {
                $scope.tabs.filters = Utility.getAttribute(data, 'filters', null);
                $scope.tabs.filter = null;
            });
        }
    });

    $scope.$watch('tabs.filter', function() {
        if ($scope.tabs.filter) {
            Restangular.one('filter', $scope.tabs.filter.id).getList('children.color', {perPage: 1000}).then(function(filters) {
                $scope.tabs.filters = filters;
                $scope.tabs.filterSets = null;
            });
        }
    });

    $scope.$watch('tabs.geonames', function() {
        if ($scope.tabs.geonames && $scope.tabs.geonames.length) {
            $scope.tabs.questionnaires = [];
            Restangular.one('geoname').get({id: _.pluck($scope.tabs.geonames, 'id').join(','), fields: 'allChildren.questionnaires,allChildren.questionnaires.permissions,questionnaires.permissions', perPage: 1000}).then(function(data) {
                var questionnaires = [];
                if (_.isArray(data)) {
                    angular.forEach(data, function(geoname) {
                        questionnaires = questionnaires.concat(Utility.getAttribute(geoname, 'questionnaires', 'read'));
                        questionnaires = questionnaires.concat(Utility.getAttribute(geoname.allChildren, 'questionnaires', 'read'));
                    });
                } else {
                    questionnaires = Utility.getAttribute(data, 'questionnaires', 'read');
                    questionnaires = questionnaires.concat(Utility.getAttribute(data.allChildren, 'questionnaires', 'read'));
                }

                $scope.tabs.questionnaires = _.uniq(questionnaires, 'id');
                $scope.tabs.surveys = null;
            });
        }
    });

    $scope.$watch('tabs.surveys', function() {
        if ($scope.tabs.surveys && $scope.tabs.surveys.length) {
            $scope.tabs.questionnaires = [];
            Restangular.one('survey').get({id: _.pluck($scope.tabs.surveys, 'id').join(','), fields: 'questionnaires.permissions', perPage: 1000}).then(function(data) {
                $scope.tabs.questionnaires = Utility.getAttribute(data, 'questionnaires', 'read');
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

            $scope.ready = false;
            $http.get('/api/table/questionnaire', {
                params: {
                    questionnaires: $scope.questionnairesIds,
                    filters: $scope.filtersIds
                }
            }).success(function(data) {
                $scope.table = data.data;

                _.forEach(data.columns, function(column) {
                    if (!_.isUndefined(column.thematic)) {
                        column.cellTemplate = "<div class='text-right ui-grid-cell-contents'>{{row.entity[col.field]}}</div>";
                    }
                });

                $scope.gridOptions.columnDefs = data.columns;
                $scope.gridOptions.headerTemplate = TableAssistant.getHeaderTemplate(data.columns);
                $scope.ready = true;
            });
        }
    }, 1000);

});
