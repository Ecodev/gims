'use strict';

/* Services */

/**
 * Simple service returning the version of the application
 */
angular.module('myApp.services', [])
    .value('version', '0.3');

/**
 * Resource service
 */
angular.module('myApp.resourceServices', ['ngResource'])
    .factory('Questionnaire', function ($resource) {
        return $resource('/api/questionnaire/:id');
    })
    .factory('Survey', function ($resource) {
        return $resource('/api/survey/:id', {}, {
            create: {
                method: 'POST'
            },
            update: {
                method: 'PUT'
            }
        });
    })
    .factory('Question', function ($resource) {
        return $resource('/api/questionnaire/:idQuestionnaire/question/:id', {}, {
            update: {
                method: 'PUT'
            }
        });
    })
    .factory('Answer', function ($resource) {
        return $resource('/api/answer/:id', {}, {
            create: {
                method: 'POST'
            },
            update: {
                method: 'PUT'
            }
        });
    })
    .factory('Country', function ($resource) {
        return $resource('/api/country/:id');
    })
    .factory('Part', function ($resource) {
        return $resource('/api/part/:id');
    })
    .factory('Filter', function ($resource) {
        return $resource('/api/filter/:id');
    })
    .factory('FilterSet', function ($resource) {
        return $resource('/api/filter-set/:id');
    })
    .factory('Select2Configurator', function ($location) {
        return {
            configure: function ($scope, Resource, key) {

                $scope.select2 = $scope.select2 || {};
                $scope.select2[key] = $scope.select2[key] || {};

                // When selected item changes, update URL
                $scope.$watch('select2.' + key + '.selected.id', function (id) {
                    if (id)
                        $location.search(key, id);
                });


                var items;
                Resource.query(function (data) {
                    console.log(1);
                    items = data;
                    var fromUrl = $location.search()[key];
                    angular.forEach(items, function (item) {
                        if (item.id == fromUrl) {
                            $scope.select2[key].selected = item;
                        }
                    });
                });

                var formatSelection = function (item) {
                    return item.name;
                };

                $scope.select2[key].list = {
                    query: function (query) {
                        var data = {results: []};

                        var searchTerm = query.term.toUpperCase();
                        var regexp = new RegExp(searchTerm);

                        angular.forEach(items, function (item) {
                            var blob = (item.id + ' ' + item.name).toUpperCase();
                            if (regexp.test(blob)) {
                                data.results.push(item);
                            }
                        });
                        query.callback(data);
                    },
                    formatResult: formatSelection,
                    formatSelection: formatSelection
                };
            }};
    });