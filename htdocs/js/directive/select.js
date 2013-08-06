/**
 * select2 wrapper for easier use within GIMS
 * Basic usage is:
 * <gims-select api="filterSet" model="mySelectedFilterSet"></gims-select>
 *
 * Or specify name attribute to change default behavior
 * <gims-select api="filterSet" model="mySelectedFilterSet" name="myFilterName" placeholder="Select a questionnaire" style="width:100%;"></gims-select>
 *
 * To enable "ID mode", specify name="id" in element. This will reload the current URL
 * with the ID of the selected item instead of GET parameter (see for example: /contribute/questionnaire)
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
            placeholder: '@',
            format: '@',
            model: '=' // TODO: could not find a way to use real 'ng-model'. So for now we use custom 'model' attribute and bi-bind it to real ng-model. Ugly, but working
        },
        // The linking function will add behavior to the template
        link: function(scope, element, attr, ctrl) {

        },
        controller: function($scope, $attrs, Restangular, $location, $route, $routeParams) {
            var api = $scope.api;
            var name = $scope.name || api; // default key to same name as route
            var fromUrl = name == 'id' ? $routeParams.id : $location.search()[name];

            // Update URL when value changes
            if (!$route.current.$$route.reloadOnSearch || name == 'id') {
                $scope.$watch($attrs.ngModel + '.id', function(id) {
                    if (id) {
                        if (name == 'id') {
                            if (id != $routeParams.id) {
                                // If curent URL reload when url changes, but we are in 'id' mode, update the ID in the URL path
                                var newPath = $location.path().replace(/\/?\d*$/, '/' + id);
                                $location.path(newPath);
                            }
                        }
                        else {
                            // If current URL does not reload when url changes, then when selected item changes, update URL search part
                            $location.search(name, id);
                        }
                    }
                });
            }

            // define what mode should be user for what type of item
            var config = {
                questionnaire: 'ajax'
            };

            // If the object type should use ajax search, then configure as such
            if (config[api] == 'ajax')
            {
                $scope.options = {
                    minimumInputLength: 1,
                    ajax: {// instead of writing the function to execute the request we use Select2's convenient helper
                        url: Restangular.all(api).getRestangularUrl(),
                        data: function(term, page) {
                            return {
                                q: term // search term
                            };
                        },
                        results: function(data, page) { // parse the results into the format expected by Select2.
                            // since we are using custom formatting functions we do not need to alter remote JSON data
                            return {results: data};
                        }
                    }
                };

                // Reload a single or multiple items if we have its ID from URL
                if (fromUrl) {
                    if (fromUrl.split(',').length > 1) {
                        Restangular.all(api).getList({id: fromUrl, returnType: 'list'}).then(function (items) {
                            $scope[$attrs.ngModel] = items;
                        });
                    } else {
                        Restangular.one(api, fromUrl).get().then(function (item) {
                            $scope[$attrs.ngModel] = item;
                        });
                    }
                }
            }
            // Otherwise, default to standard mode (list fully loaded)
            else
            {
                // Load items and re-select item based on URL params (if any)
                var items;
                Restangular.all(api).getList().then(function(data) {

                    // @todo clean me up! For demo purposes role list must be filtered...
                    if (api === 'role') {
                        angular.forEach(data, function (item, index) {
                            if (item.id !== 4 && item.id !== 3) {
                                delete data[index];
                            }
                        });
                    }
                    items = data;
                    angular.forEach(items, function(item) {
                        if (item.id == fromUrl) {
                            $scope[$attrs.ngModel] = item;
                        }
                    });
                });

                $scope.options = {
                    query: function(query) {
                        var data = {results: []};

                        var searchTerm = query.term.toUpperCase();
                        var regexp = new RegExp(searchTerm);

                        angular.forEach(items, function(item) {

                            var result = item.name;
                            // @todo fix me! We should have a way to define the format key. Case added for survey.
                            if (item.code !== undefined) {
                                result = item.code;
                            }
                            var blob = (item.id + ' ' + item.name).toUpperCase();
                            if (regexp.test(blob)) {
                                data.results.push(item);
                            }
                        });
                        query.callback(data);
                    }
                };
            }

            // Configure formatting
            var formatSelection = function(item) {
                var result = item.name;
				if ($scope.format == 'code')
				{
					result = item.code || item.id || item.name;
				}
                return result;
            };

            $scope.options.formatResult = formatSelection;
            $scope.options.formatSelection = formatSelection;

            // Required to be able to clear the selected value (used in directive gimsRelation)
            $scope.options.initSelection = function(element, callback) {
                callback($(element).data('$ngModelController').$modelValue);
            };
        }
    };
});