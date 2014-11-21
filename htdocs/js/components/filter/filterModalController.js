angular.module('myApp').controller('FilterModalCtrl', function($scope, $modalInstance, $timeout, Restangular, params, TableFilter, Modal) {
    'use strict';

    $scope.data = {};
    $scope.data.expandedNodes = [];
    $scope.data.selected = params.selected ? params.selected : [];

    var queryParams = params.queryParams ? params.queryParams : {fields: 'color'};
    var flattenedTree = null;

    $scope.data.treeOptions = {
        dirSelectable: true,
        injectClasses: {
            li: "text-2em",
            iExpanded: "fa fa-minus text-2em fa-fw",
            iCollapsed: "fa fa-plus text-2em fa-fw",
            iLeaf: "fa fa-fw"
        },

        // necessary because some filter are in multiple places and there are conflicts without strict equality
        equality: function(node1, node2) {
            return node1 === node2;
        }
    };

    $scope.$watch('data.tree', function() {
        expandRootNodes();
        flattenedTree = getFlattenedTree($scope.data.tree);
    });

    /**
     * Watch for search teams to deploy all hierarchy to show all results instead of just root filters
     * When deleting search chars, deploy hierarchy according to selected elements in order to show everything that is selected
     */
    $scope.$watch('data.search', function() {
        if ($scope.data.search) {
            $scope.data.expandedNodes = flattenedTree;
        } else {
            if (!_.isEmpty($scope.data.selected)) {
                $scope.data.expandedNodes = [];
                searchAndOpenFoldersWithSelectedElements($scope.data.tree);
            } else {
                expandRootNodes();
            }
        }
    });

    var expandRootNodes = function() {
        var rootNodes = [];
        _.forEach($scope.data.tree, function(filter) {
            rootNodes.push(filter);
        });

        $scope.data.expandedNodes = rootNodes;
    };

    var getFlattenedTree = function(tree) {
        var elements = [];
        _.forEach(tree, function(filter) {
            if (!_.isEmpty(filter.children)) {
                elements.push(filter);
                elements = elements.concat(getFlattenedTree(filter.children));
            }
        });

        return elements;
    };

    var searchAndOpenFoldersWithSelectedElements = function(elements) {
        var isSelected = false;
        angular.forEach(elements, function(node) {
            if (searchAndOpenFoldersWithSelectedElements(node.children)) {
                isSelected = true;
                $scope.data.expandedNodes.push(node);
            }

            if (node.selected) {
                isSelected = true;
            }
        });

        return isSelected;
    };

    var initSelected = function(elements) {
        _.forEach(elements, function(node) {

            // if node is selected, mark or unmark it
            var found = _.find($scope.data.selected, function(el) {
                return node.id == el.id;
            });

            if (found) {
                addToSelected(node);
            } else {
                removeFromSelected(node);
            }

            // repeat operation for children
            initSelected(node.children);
        });
    };

    var tagAllSimilarFilters = function(filter, filters) {
        _.forEach(filters, function(f) {
            if (f.id == filter.id) {
                f.selected = filter.selected;
            } else {
                tagAllSimilarFilters(filter, f.children);
            }
        });
    };

    var addToSelected = function(filter) {

        if (!params.multiple) {
            _.forEach($scope.data.selected, function(el) {
                removeFromSelected(el);
            });
        }

        filter.selected = true;
        var found = _.find($scope.data.selected, function(s) {
            return s.id == filter.id;
        });

        if (!found) {
            $scope.data.selected.push(filter);
        }

        tagAllSimilarFilters(filter, $scope.data.tree);
    };

    var removeFromSelected = function(filter) {
        filter.selected = false;
        _.remove($scope.data.selected, function(f) {
            return f.id == filter.id;
        });
        tagAllSimilarFilters(filter, $scope.data.tree);
    };

    TableFilter.getTree(queryParams).then(function(tree) {
        $scope.data.tree = tree;
        initSelected($scope.data.tree);
        searchAndOpenFoldersWithSelectedElements($scope.data.tree);
    });

    var lastSelected = null;
    $scope.data.showSelected = function(selection) {

        if (selection) {
            lastSelected = selection;
        } else {
            selection = lastSelected;
        }

        if (selection) {
            if (!selection.selected) {
                addToSelected(selection);
            } else {
                removeFromSelected(selection);
            }

            $scope.data.selectedNode = selection;
        }
    };

    $scope.data.selectFilters = function() {
        if (!params.multiple) {
            $scope.data.selected = $scope.data.selected[0];
        }
        $modalInstance.close($scope.data.selected);
    };

    $scope.data.remove = function(filter) {
        Modal.confirmDelete(filter, {returnUrl: '/admin/filter'}).then(function() {
            TableFilter.getTree(queryParams, true).then(function(tree) {
                $scope.data.tree = tree;
            });
        });

    };

    $scope.data.$dismiss = function() {
        $modalInstance.dismiss();
    };

});
