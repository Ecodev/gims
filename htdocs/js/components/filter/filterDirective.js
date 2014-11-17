/*
 Usage :

 <gims-filter
     model='filters'            -> usual model
     name="filters"             -> optional name (reported on url)
     generic-color="false"       -> if true, show a color for each filter (genericColor property), if false, show only color property
     multiple                   -> select2 in tag mode (missing means single selection on select2)
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
        replace: true,
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
            showEditButton: '@'
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
            var getSelected = function(filters, selectionIds) {
                var selected = [];
                _.forEach(filters, function(filter) {
                    var found = _.find(selectionIds, function(id) {
                        return filter.id == parseInt(id);
                    });

                    if (found) {
                        selected.push(filter);
                    }
                    selected = selected.concat(getSelected(filter.children, selectionIds));
                });

                return selected;
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

            // Url
            var ids = $location.search()[scope.name];
            if (ids) {
                TableFilter.getTree(scope.queryParams).then(function(filters) {
                    tree = filters;
                    scope.model = getSelected(tree, ids.split(','));
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
