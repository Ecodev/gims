/**
 * ui-select wrapper for easier use within GIMS
 * Basic usage is:
 * <gims-select api="filterSet" model="mySelectedFilterSet"></gims-select>
 *
 * Or specify name attribute to change default behavior
 * <gims-select api="filterSet" model="mySelectedFilterSet" name="myFilterName" placeholder="Select a questionnaire" style="width:100%;"></gims-select>
 *
 * To enable "ID mode", specify name="id" in element. This will reload the current URL
 * with the ID of the selected item instead of GET parameter (see for example: /contribute/jmp)
 *
 * To specify additional GET parameter for API calls, use attribute queryparams:
 * <gims-select api="questionnaire" queryparams="questionnaireQueryParams" /></gims-select>
 */
angular.module('myApp.directives').directive('gimsSelect', function() {
    'use strict';

    var choicesTemplate =
            '<ui-select-choices repeat="item in data.items track by item.id" refresh="loadChoices($select.search)" refresh-delay="200">' +
            '    <div ng-bind-html="(item.code ? item.code + \' - \' : \'\') + item.name | highlight: $select.search"></div>' +
            '</ui-select-choices>';

    return {
        restrict: 'E', // Only usage possible is with element
        require: 'ngModel',
        replace: true,
        template:
                '<span style="display: inline-block;">' +
                // Single
                '    <ui-select ng-model="data.selected" ng-disabled="disabled" reset-search-input="true" ng-if="::!multiple" >' +
                '        <ui-select-match placeholder="{{::data.placeholder}}" class="ui-select-match">{{($select.selected.code ? $select.selected.code + \' - \' : \'\') +  $select.selected.name}}</ui-select-match>' +
                choicesTemplate +
                '    </ui-select>' +
                // Multiple
                '    <ui-select ng-model="data.selected" ng-disabled="disabled" reset-search-input="true" ng-if="::multiple" multiple="multiple">' +
                '        <ui-select-match placeholder="{{::data.placeholder}}" class="ui-select-match">{{($item.code ? $item.code + \' - \' : \'\') +  $item.name}}</ui-select-match>' +
                choicesTemplate +
                '    </ui-select>' +
                '<span>',
        scope: {
            api: '@',
            name: '@',
            placeholder: '@',
            changeUrl: '=',
            queryparams: '=',
            disabled: '=',
            model: '=' // TODO: could not find a way to use real 'ng-model'. So for now we use custom 'model' attribute and bi-bind it to real ng-model. Ugly, but working
        },
        controller: function($scope, $attrs, Restangular, CachedRestangular, $location, $route, $routeParams) {
            var api = $scope.api;
            var name = $scope.name || api; // default key to same name as route
            var fromUrl = name == 'id' ? $routeParams.id : $location.search()[name];
            var changeUrl = $scope.changeUrl === false ? false : true;

            var idsFromUrl = [];
            if (_.isString(fromUrl)) {
                idsFromUrl = _.map(fromUrl.split(','), function(id) {
                    return parseInt(id);
                });
            } else if (_.isNumber(fromUrl)) {
                idsFromUrl = [fromUrl];
            }

            $scope.multiple = !_.isUndefined($attrs.multiple);

            // define what mode should be used for what type of item
            // Items with small quantity should be cached once and for all,
            // items with large quantity should use ajax.
            var config = {
                user: 'ajax',
                survey: 'ajax',
                questionnaire: 'ajax',
                rule: 'ajax',
                filter: 'ajax',
                filterSet: 'cached',
                geoname: 'cached',
                part: 'cached'
            };

            // Synchronize internal model with external model
            $scope.$watch('model', function(model) {

                if (!_.isUndefined(model)) {
                    // If we are multiple, we must guarantee that the newly selected
                    // items exist in the choices and it is exactly the same object
                    if ($scope.multiple) {

                        if (!$scope.data.items) {
                            $scope.data.items = [];
                        }

                        _.forEach(model, function(item) {

                            // First remove equivalent object from list (same ID)
                            _.remove($scope.data.items, {id: item.id});

                            // Then add the one we selected
                            $scope.data.items.push(item);
                        });
                    }

                    $scope.data.selected = model;
                }
            });

            // Synchronize external model with internal model
            $scope.$watch('data.selected', function(selected) {
                if (!_.isUndefined(selected)) {
                    $scope.model = selected;
                }
            });

            // Use cached version if configuration ask for it
            var myRestangular = config[api] == 'cached' ? CachedRestangular : Restangular;

            $scope.data = {
                selected: $scope.multiple ? [] : null,
                placeholder: $scope.placeholder || 'Select ' + $attrs.api
            };

            var isFirstLoad = true;
            $scope.loadChoices = function(search) {
                if (!reSelectFromUrl()) {
                    load(search);
                }

                isFirstLoad = false;
            };

            /**
             * Load items from server, with optionnal search.
             * This will be called to pre-load data when directive initialize, and
             * then everytime the user search for something
             * @param {string} search
             * @returns {undefined}
             */
            function load(search) {
                var params = _.merge({q: search}, $scope.queryparams);
                myRestangular.all(api).getList(params).then(function(choices) {
                    $scope.data.items = choices;
                });
            }

            /**
             * Reload a single or multiple items if we have its ID from URL
             */
            function reSelectFromUrl() {
                if (!isFirstLoad) {
                    return false;
                }

                if (idsFromUrl.length == 1) {
                    myRestangular.one(api, fromUrl).get($scope.queryparams).then(function(item) {

                        // If in multiple mode, we need to return an array, even if we have a single ID
                        if ($scope.multiple) {
                            item = [item];
                        }

                        $scope.data.selected = item;
                    });
                    return true;
                } else if (idsFromUrl.length > 1) {
                    myRestangular.all(api).getList(_.merge({id: fromUrl}, $scope.queryparams)).then(function(items) {
                        $scope.data.selected = items;
                    });
                    return true;
                }

                // If reaches here, means we did not find any ID and could not load anything
                return false;
            }

            // in case we don't want url to be affected, we don't want neither to display received info, so remove it after retrieve it above
            if (!changeUrl) {
                $location.search(name, null);
            }

            // Update URL when value changes
            if (!$route.current.$$route.reloadOnSearch && changeUrl || name == 'id') {
                $scope.$watch('data.selected', function(o) {

                    // Don't do anything, until we loaded choices for the first time
                    // This avoid URL parameters to disappear and re-appear
                    if (isFirstLoad) {
                        return;
                    }

                    // Either get the single ID, or the multiple IDs
                    var id;
                    if (_.isString(o)) {
                        id = o;
                    } else if (o && o.id) {
                        id = o.id;
                    } else if (o) {
                        id = _.map(o, function(a) {
                            return a.id;
                        }).join(',');
                    }

                    if (!_.isUndefined(id)) {
                        if (name == 'id') {
                            if (id != $routeParams.id) {
                                // If curent URL reload when url changes, but we are in 'id' mode, update the ID in the URL path
                                var newPath = $location.path().replace(/\/?\d*$/, '/' + id);
                                $location.path(newPath);
                            }
                        } else {
                            // If current URL does not reload when url changes, then when selected item changes, update URL search part
                            if (id === '') {
                                id = null;
                            }
                            $location.search(name, id);
                        }
                    }
                });
            }
        }
    };
});
