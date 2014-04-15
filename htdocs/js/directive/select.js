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
 *
 * To set advanced templates on list elements, send string template via custom-selection-template and custom-format-template attribute
 * Dynamic content is eval() by javascript. Escape content to eval with [[expression]].
 * Complexe structures like functions are evaluated too
 * <gims-select custom-selection-template="This element is [[item.name]] and is is [[item.id]]." /></gims-select>
 * <gims-select custom-result-template="[[_.map(item.paths, function(path){return '<i class=\'fa fa-gims-filter\'></i> \'+path+\'';}).join('')]]"/></gims-select>
 * <gims-select custom-selection-template="{{template}}"/></gims-select>
 *
 * The css class select2list allow to display list elements in the select2 in full with. It allows advanced styling by custom-template attribute
 * <gims-select container-css-class="select2list" /></gims-select>
 *
 * current-context-element attribute is used only in custom-template. Allow to generate content using current page context id (e.g using return URL id)
 * <gims-select current-context-element="{{filter.id}}" custom-selection-template="<a href='returnUrl=/admin/xxx/edit/[[$scope.currentContextElement]]'>edit</a>" /></gims-select>
 *
 * To see more about custom-template usage, see admin/filter.js controller.
 */
angular.module('myApp.directives').directive('gimsSelect', function() {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with element
        require: 'ngModel',
        replace: true,
        transclude: true,
        template: '<input type="hidden" ui-select2="options" ng-model="model" ng-disabled="disabled"/>',
        scope: {
            api: '@',
            name: '@',
            placeholder: '@',
            format: '@',
            customSelectionTemplate: '@',
            customResultTemplate: '@',
            containerCssClass: '@',
            currentContextElement: '@',
            changeUrl: '=',
            queryparams: '=',
            disabled: '=',
            model: '=' // TODO: could not find a way to use real 'ng-model'. So for now we use custom 'model' attribute and bi-bind it to real ng-model. Ugly, but working
        },
        // The linking function will add behavior to the template
        link: function() {
        },
        controller: function($scope, $attrs, Restangular, CachedRestangular, $location, $route, $routeParams, $timeout) {
            var items = [];
            var api = $scope.api;
            var name = $scope.name || api; // default key to same name as route
            var fromUrl = name == 'id' ? $routeParams.id : $location.search()[name];
            var idsFromUrl = fromUrl ? fromUrl.split(',') : [];
            var changeUrl = $scope.changeUrl === false ? false : true;
            idsFromUrl = _.map(idsFromUrl, function(id) {
                return parseInt(id);
            });

            $scope.includeLinks = function() {
                $timeout(function() {
                    $('.select2list .btn[href]').off('mouseup').on('mouseup', function() {
                        location.href = $(this).attr('href');
                    });
                }, 500);
            };

            // Update URL when value changes
            if (!$route.current.$$route.reloadOnSearch && changeUrl || name == 'id') {
                $scope.$watch($attrs.ngModel, function(o) {

                    // Either get the single ID, or the multiple IDs
                    var id;
                    if (_.isString(o)) {
                        id = o;
                    } else if (o && o.id) {
                        id = o.id;
                    } else if (o) {
                        id = _.map(o,function(a) {
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
                            $location.search(name, id);
                        }
                    }
                });
            }

            // define what mode should be used for what type of item
            var config = {
                questionnaire: 'ajax',
                user: 'ajax',
                country: 'cached',
                part: 'cached'
            };

            // Use cached version if configuration ask for it
            var myRestangular = config[api] == 'cached' ? CachedRestangular : Restangular;

            // If the object type should use ajax search, then configure as such
            if (config[api] == 'ajax') {
                $scope.options = {
                    minimumInputLength: 1,
                    ajax: {// instead of writing the function to execute the request we use Select2's convenient helper
                        url: myRestangular.all(api).getRestangularUrl(),
                        data: function(term) {
                            return _.merge({q: term}, $scope.queryparams);
                        },
                        results: function(data) { // parse the results into the format expected by Select2.

                            // Make sure to have Restangular object
                            items = _.map(data.items, function(item) {
                                return Restangular.restangularizeElement(null, item, api);
                            });

                            return {results: items};
                        }
                    }
                };

                // Reload a single or multiple items if we have its ID from URL
                if (idsFromUrl.length == 1) {
                    myRestangular.one(api, fromUrl).get($scope.queryparams).then(function(item) {

                        // If select2 is in multiple mode, we need to return an array, even if we have a single ID
                        if (!_.isUndefined($attrs.multiple)) {
                            item = [item];
                        }

                        $scope[$attrs.ngModel] = item;
                        $scope.includeLinks();
                    });
                } else if (idsFromUrl.length > 1) {
                    myRestangular.all(api).getList(_.merge({id: fromUrl}, $scope.queryparams)).then(function(items) {
                        $scope[$attrs.ngModel] = items;
                        $scope.includeLinks();
                    });
                }
            }
            // Otherwise, default to standard mode (list fully loaded)
            else {
                // Load items and re-select item based on URL params (if any)
                myRestangular.all(api).getList(_.merge({perPage: 1000}, $scope.queryparams)).then(function(data) {

                    items = data;

                    if (idsFromUrl.length) {
                        var selectedItems = [];
                        _.forEach(idsFromUrl, function(idFromUrl) {
                            _.forEach(items, function(item) {
                                if (item.id == idFromUrl) {
                                    selectedItems.push(item);
                                }
                            });
                        });

                        // If we are not multiple, we need to return an object, not an array of objects
                        if (_.isUndefined($attrs.multiple)) {
                            selectedItems = _.first(selectedItems);
                        }

                        $scope[$attrs.ngModel] = selectedItems;
                    }
                    $scope.includeLinks();
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
                                result += ' ' +item.code;
                            }
                            var blob = (item.id + ' ' + result).toUpperCase();
                            if (regexp.test(blob)) {
                                data.results.push(item);
                            }
                        });
                        query.callback(data);
                    }
                };
            }

            // Configure selection formatting
            var formatSelection = function(item) {
                var result;
                if ($scope.customSelectionTemplate) {
                    result = generateTemplate(item, $scope.customSelectionTemplate);
                } else {
                    result = formatStandardTemplate(item);
                }

                return result;
            };

            // Configure result formatting
            var formatResult = function(item) {
                var result;
                if ($scope.customResultTemplate) {
                    result = generateTemplate(item, $scope.customResultTemplate);
                } else {
                    result = formatStandardTemplate(item);
                }

                return result;
            };

            var formatStandardTemplate = function(item) {
                var result = item.name;
                if ($scope.format == 'code') {
                    result = item.code || item.id || item.name;
                }

                return result;
            };

            /**
             * Generate custom template by finding tags [[xxx]] and evaluating them, and then replacing in the template.
             *
             * @param item element of list with queryparams requested. This var is used only by the custom-template string.
             * @returns {string}
             */
            var generateTemplate = function(item, originalTemplate) {
                // copy orignal template to avoid remplacing elements on it
                var template = originalTemplate;

                // find tags
                var matches = template.match(/\[\[(.*?)\]\]/gi);

                // eval() each tag and replace in template without replacing original $scope.customTemplate
                for (var matchKey in matches) {
                    var match = matches[matchKey];
                    var evaluatedString = eval(match.substring(2, match.length - 2)); // substring remove the begining "[[" and the ending "]]"
                    if (!evaluatedString) {
                        evaluatedString = '';
                    }
                    template = template.replace(match, evaluatedString);
                }

                return template;
            };

            // override original excapeMarkup function allowing to return html content.
            if ($scope.customSelectionTemplate || $scope.customResultTemplate) {
                $scope.options.escapeMarkup = function(m) {
                    return m;
                };
            }

            $scope.options.formatResult = formatResult;
            $scope.options.formatSelection = formatSelection;
            $scope.options.containerCssClass = $scope.containerCssClass;

            // Required to be able to clear the selected value (used in directive gimsRelation)
            $scope.options.initSelection = function(element, callback) {
                var controller = $(element).data('$ngModelController');
                var selectedId = controller ? controller.$modelValue : null;

                var selectedItem = _.find(items, function(item) {
                    return item.id == selectedId;
                });

                callback(selectedItem);
            };
        }
    };
});