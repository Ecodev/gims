/**
 * select2 wrapper for easier use within GIMS
 * Basic usage is:
 * <gims-select api="filterSet" nmodel="mySelectedFilterSet"></gims-select>
 *
 * Or specify name attribute to change default behavior
 * <gims-select api="filterSet" model="mySelectedFilterSet" name="myFilterName" placeholder="Select a questionnaire" style="width:100%;"></gims-select>
 */
angular.module('myApp.directives').directive('gimsSelect', function() {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with attribute
        require: 'ngModel',
        replace: true,
        transclude: true,
        template: '<input ui-select2="options" ng-model="model"/>',
        scope: {
            api: '@',
            name: '@',
            model: '=' // TODO: could not find a way to use real 'ng-model'. So for now we use custom 'model' attribute and bi-bind it to real ng-model. Ugly, but working
        },
        // The linking function will add behavior to the template
        link: function(scope, element, attr, ctrl) {

        },
        controller: function($scope, $attrs, Restangular, $location, $route) {
            var api = $scope.api;
            var name = $scope.name || api; // default key to same name as route

            // If current URL does not reload when url changes, then when selected item changes, update URL
            if (!$route.current.$$route.reloadOnSearch) {
                $scope.$watch($attrs.ngModel + '.id', function(id) {
                    if (id) {
                        $location.search(name, id);
                    }
                });
            }

            // Load items and, re-select item based on URL params (if any)
            var items;
            Restangular.all(api).getList().then(function(data) {
                items = data;
                var fromUrl = $location.search()[name];
                angular.forEach(items, function(item) {
                    if (item.id == fromUrl) {
                        $scope[$attrs.ngModel] = item;
                    }
                });
            });

            // Configure Select2
            var formatSelection = function(item) {
                return item.name;
            };

            $scope.options = {
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

        }
    };
});