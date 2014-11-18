/*
 Usage :

 <gims-filter
     model='filters'            -> usual model
     name="filters"             -> optional name (reported on url)
     generic-color="false"      -> if true, show a color for each filter (genericColor property), if false, show only color property
     multiple="false"           -> must contain a value "true" or "false", if attribute present but missing value, will be false
     query-params="queryParams" -> params for query. color,genericColor,paths are automatically added depending on above attributes values
     change-url="true"          -> update url with ids on selection change
     show-edit-button="true"    -> show a button that links to /admin/filter/x page
     >
 </gims-filter>
 */
angular.module('myApp.directives').directive('gimsFilter', function(FilterModal, Restangular, $location, TableFilter) {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with element
        templateUrl: '/js/components/filter/templates/filterDirective.phtml',
        scope: {
            model: '=model',
            name: '@',
            multiple: '@?',
            queryParams: '=?',
            disabled: '=?',
            changeUrl: '@',
            genericColor: '@',
            bgColor: '@',
            showEditButton: '@',
            dependencies: '@'
        },
        link: function(scope) {

            // init vars
            scope.ready = false;
            scope.disabled = !_.isUndefined(scope.disabled) ? (scope.disabled === 'true' ? true : false) : false;
            scope.showEditButton = !_.isUndefined(scope.showEditButton) ? (scope.showEditButton === 'true' ? true : false) : false;
            scope.showDeleteButton = !_.isUndefined(scope.showDeleteButton) ? (scope.showDeleteButton === 'true' ? true : false) : false;
            scope.multiple = !_.isUndefined(scope.multiple) ?(scope.multiple === 'true' ? true : false): false;

            var tree = null;
            var genericColor = !_.isUndefined(scope.genericColor) ? (scope.genericColor === 'true' ? true : false) : false;
            var bgColor = !_.isUndefined(scope.bgColor) ? (scope.bgColor === 'true' ? true : false) : false;
            var changeUrl = !_.isUndefined(scope.changeUrl) ? (scope.changeUrl === 'true' ? true : false) : true;

            // complete params
            var defaultFields = ['color'];
            if (genericColor) {
                defaultFields.push('genericColor');
            }
            if (bgColor) {
                defaultFields.push('bgColor');
            }

            if (!scope.queryParams) {
                scope.queryParams = {fields: defaultFields.join(',')};
            } else {
                var userFields = scope.queryParams.fields ? scope.queryParams.fields.split(',') : [];
                var finalFields = _.uniq(defaultFields.concat(userFields));
                scope.queryParams.fields = finalFields.join(',');
            }

            scope.$watchCollection('model', function() {
                if (scope.model) {

                    // transform model to array if multiple selection
                    if (scope.multiple && !_.isArray(scope.model)) {
                        scope.model = [scope.model];

                        // transform model to single object if single selection
                    } else if (!scope.multiple && _.isArray(scope.model)) {
                        scope.model = scope.model[0] ? scope.model[0] : undefined;
                    }

                    // scope.elements is the internal representation of model and is always an array for templating simplification
                    if (!_.isArray(scope.model)) {
                        scope.elements = [scope.model];
                    } else {
                        scope.elements = scope.model;
                    }

                    updateUrl();
                }
            });

            // private functions
            var getSelectedElements = function(filters, selectionIds) {
                var selected = [];
                _.forEach(filters, function(filter) {
                    var found = _.find(selectionIds, function(id) {
                        return filter.id == parseInt(id);
                    });

                    if (found) {
                        selected.push(filter);
                    }
                    selected = selected.concat(getSelectedElements(filter.children, selectionIds));
                });

                return _.uniq(selected, 'id');
            };

            var getSelected = function(filters, selectionIds) {
                filters = getSelectedElements(filters, selectionIds);
                var elements = [];
                _.forEach(selectionIds, function(selectionId) {
                    elements.push(_.find(filters, function(f) {
                        return f.id == selectionId;
                    }));
                });
                return elements;
            };

            // scoped functions
            scope.remove = function(filter) {
                filter.selected = false;
                _.remove(scope.elements, function(f) {
                    return f.id == filter.id;
                });
            };

            scope.openModal = function() {
                var params = {
                    multiple: scope.multiple,
                    selected: _.clone(scope.elements),
                    queryParams: scope.queryParams
                };

                FilterModal.select(params).then(function(selection) {
                    scope.model = selection;
                });
            };

            /**
             * Check if there are dependencies that will affect model (like filter set and filter)
             * If no dependency exist in url, load filters specified querystring
             */
            var dependencies = scope.dependencies ? scope.dependencies.split(',') : [];
            var hasDependencies = false;
            _.forEach(dependencies, function(dependency) {
                if ($location.search()[dependency] && $location.search()[dependency].split(',').length) {
                    hasDependencies = true;
                    return false;
                }
            });

            var ids = $location.search()[scope.name];
            if (!hasDependencies && ids) {
                TableFilter.getTree(scope.queryParams).then(function(treeFilters) {

                    // retrieve filters
                    tree = treeFilters;
                    var filters = getSelected(tree, ids.split(','));

                    // offset filters to avoid negative positionning
                    var minLevel = _.min(filters, 'level').level;
                    filters[0].offsetLevel = _.first(filters).level - minLevel;

                    // update model
                    scope.model = filters;
                    scope.ready = true;
                });
            } else {
                scope.ready = true;
            }

            var updateUrl = function() {
                if (changeUrl) {
                    if (scope.elements && scope.elements.length) {
                        $location.search(scope.name, _.pluck(scope.elements, 'id').join(','));
                    } else {
                        $location.search(scope.name, null);
                    }
                }
            };

        }
    };
});
