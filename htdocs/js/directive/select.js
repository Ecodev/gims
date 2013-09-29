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
 *
 * To specify additional GET parameter for API calls, use attribute queryparams:
 * <gims-select api="questionnaire" queryparams="questionnaireQueryParams" /></gims-select>
 */
angular.module('myApp.directives').directive('gimsSelect', function() {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with element
        require: 'ngModel',
        replace: true,
        transclude: true,
        template: '<input ui-select2="options" ng-model="model"/>',
        scope: {
            api: '@',
            name: '@',
            placeholder: '@',
            format: '@',
            queryparams: '=',
            model: '=' // TODO: could not find a way to use real 'ng-model'. So for now we use custom 'model' attribute and bi-bind it to real ng-model. Ugly, but working
        },
        // The linking function will add behavior to the template
        link: function(scope, element, attr, ctrl) {

        },
        controller: function($scope, $attrs, Restangular, CachedRestangular, $location, $route, $routeParams) {

            var api = $scope.api;
            var name = $scope.name || api; // default key to same name as route
            var fromUrl = name == 'id' ? $routeParams.id : $location.search()[name];
            var idsFromUrl = fromUrl ? fromUrl.split(',') : [];
            idsFromUrl = _.map(idsFromUrl, function(id) {
                return parseInt(id);
            });

            // Update URL when value changes
            if (!$route.current.$$route.reloadOnSearch || name == 'id') {
                $scope.$watch($attrs.ngModel, function(o) {

                    // Either get the single ID, or the multiple IDs
                    var id;
                    if (o && o.id) {
                        id = o.id;
                    }
                    else if (o) {
                        id = _.map(o, function(a) { return a.id; }).join(',');
                    }

                    if (!_.isUndefined(id)) {
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

            // define what mode should be used for what type of item
            var config = {
                questionnaire: 'ajax',
                country: 'cached',
                part: 'cached'
            };

            // Use cached version if configuration ask for it
            var myRestangular = config[api] == 'cached' ? CachedRestangular : Restangular;

            // If the object type should use ajax search, then configure as such
            if (config[api] == 'ajax')
            {
                $scope.options = {
                    minimumInputLength: 1,
                    ajax: {// instead of writing the function to execute the request we use Select2's convenient helper
                        url: myRestangular.all(api).getRestangularUrl(),
                        data: function(term, page) {
                            return _.merge({q: term}, $scope.queryparams);
                        },
                        results: function(data, page) { // parse the results into the format expected by Select2.
                            // since we are using custom formatting functions we do not need to alter remote JSON data
                            return {results: data};
                        }
                    }
                };

                // Reload a single or multiple items if we have its ID from URL
                if (idsFromUrl.length == 1) {
                    myRestangular.one(api, fromUrl).get($scope.queryparams).then(function(item) {
                        $scope[$attrs.ngModel] = item;
                    });
                } else if (idsFromUrl.length > 1) {
                    myRestangular.all(api).getList(_.merge({id: fromUrl}, $scope.queryparams)).then(function(items) {
                        $scope[$attrs.ngModel] = items;
                    });
                }
            }
            // Otherwise, default to standard mode (list fully loaded)
            else
            {
                // Load items and re-select item based on URL params (if any)
                var items;
                myRestangular.all(api).getList($scope.queryparams).then(function(data) {

                    items = data;
                    var selectedItems = _.filter(items, function(item) {
                        return _.contains(idsFromUrl, item.id);
                    });

                    // If we are not multiple, we need to return an object, not an array of objects
                    if (!$attrs.multiple) {
                        selectedItems = _.first(selectedItems);
                    }
                    
                    $scope[$attrs.ngModel] = selectedItems;
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