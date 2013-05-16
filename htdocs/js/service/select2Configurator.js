
angular.module('myApp.services').factory('Select2Configurator', function($location, $route) {
    'use strict';

    return {
        configure: function($scope, Resource, key) {

            $scope.select2 = $scope.select2 || {};
            $scope.select2[key] = $scope.select2[key] || {};

            // If current URL does not reload when url changes, then when selected item changes, update URL
            if (!$route.current.$$route.reloadOnSearch) {
                $scope.$watch('select2.' + key + '.selected.id', function(id) {
                    if (id)
                        $location.search(key, id);
                });
            }


            var items;
            Resource.query(function(data) {
                items = data;
                var fromUrl = $location.search()[key];
                angular.forEach(items, function(item) {
                    if (item.id == fromUrl) {
                        $scope.select2[key].selected = item;
                    }
                });
            });

            var formatSelection = function(item) {
                return item.name;
            };

            $scope.select2[key].list = {
                query: function(query) {
                    var data = {results: []};

                    var searchTerm = query.term.toUpperCase();
                    var regexp = new RegExp(searchTerm);

                    angular.forEach(items, function(item) {
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