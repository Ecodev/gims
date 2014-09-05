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
        if ($scope.tabs.filterSets && $scope.tabs.filterSets.length) {
            $scope.tabs.filters = [];
            Restangular.one('filterSet').get({id: _.pluck($scope.tabs.filterSets, 'id').join(','), fields: 'filters', perPage: 1000}).then(function(data) {
                $scope.tabs.filters = getAttribute(data, 'filters', null);
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

    /**
     * Return a parameter in a list of objects (or single object)
     * Almost could use _.pluck but the loop in pluck dont use angular.foreach and uses some angular function as object. pluck can't neither differentiate if server returns list or single object
     * @param data
     * @param attribute
     * @param permission
     * @returns {Array}
     */
    var getAttribute = function(data, attribute, permission) {
        var list = [];
        if (_.isArray(data)) {
            angular.forEach(data, function(obj) {
                list = list.concat(obj[attribute]);
            });
        } else if (data) {
            list = data[attribute];
        }

        list = _.filter(list, function(l) {
            if (!permission || permission && l && (!l.permissions || l.permissions && l.permissions[permission])) {
                return true;
            }
            return false;
        });

        return list;
    };

    $scope.$watch('tabs.geonames', function() {
        if ($scope.tabs.geonames && $scope.tabs.geonames.length) {
            $scope.tabs.questionnaires = [];
            Restangular.one('geoname').get({id: _.pluck($scope.tabs.geonames, 'id').join(','), fields: 'allChildren.questionnaires,allChildren.questionnaires.permissions,questionnaires.permissions', perPage: 1000}).then(function(data) {
                var questionnaires = [];
                if (_.isArray(data)) {
                    angular.forEach(data, function(geoname) {
                        questionnaires = questionnaires.concat(getAttribute(geoname, 'questionnaires', 'read'));
                        questionnaires = questionnaires.concat(getAttribute(geoname.allChildren, 'questionnaires', 'read'));
                    });
                } else {
                    questionnaires = getAttribute(data, 'questionnaires', 'read');
                    questionnaires = questionnaires.concat(getAttribute(data.allChildren, 'questionnaires', 'read'));
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
                $scope.tabs.questionnaires = getAttribute(data, 'questionnaires', 'read');
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
