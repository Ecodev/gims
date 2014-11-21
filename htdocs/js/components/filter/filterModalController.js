angular.module('myApp').controller('FilterModalCtrl', function($scope, $modalInstance, $timeout, Restangular, params, TableFilter, Modal) {
    'use strict';

    $scope.selected = params.selected ? params.selected : [];
    var queryParams = params.queryParams ? params.queryParams : {fields: 'color'};
    $scope.expandedNodes = [];

    $scope.$watch('tree', function() {
        _.forEach($scope.tree, function(filter) {
            $scope.expandedNodes.push(filter);
        });
    });

    $scope.treeOptions = {
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

    var searchAndOpenFoldersWithSelectedElements = function(elements) {
        var isSelected = false;
        angular.forEach(elements, function(node) {
            if (searchAndOpenFoldersWithSelectedElements(node.children)) {
                isSelected = true;
                $scope.expandedNodes.push(node);
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
            var found = _.find($scope.selected, function(el) {
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
            _.forEach($scope.selected, function(el) {
                removeFromSelected(el);
            });
        }

        filter.selected = true;
        var found = _.find($scope.selected, function(s) {
            return s.id == filter.id;
        });

        if (!found) {
            $scope.selected.push(filter);
        }

        tagAllSimilarFilters(filter, $scope.tree);
    };

    var removeFromSelected = function(filter) {
        filter.selected = false;
        _.remove($scope.selected, function(f) {
            return f.id == filter.id;
        });
        tagAllSimilarFilters(filter, $scope.tree);
    };

    TableFilter.getTree(queryParams).then(function(tree) {
        $scope.tree = tree;
        initSelected($scope.tree);
        searchAndOpenFoldersWithSelectedElements($scope.tree);
    });

    var lastSelected = null;
    $scope.showSelected = function(selection) {

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

            $scope.selectedNode = selection;
        }
    };

    $scope.selectFilters = function() {
        if (!params.multiple) {
            $scope.selected = $scope.selected[0];
        }
        $modalInstance.close($scope.selected);
    };

    $scope.remove = function(filter) {
        Modal.confirmDelete(filter, {returnUrl: '/admin/filter'}).then(function() {
            TableFilter.getTree(queryParams, true).then(function(tree) {
                $scope.tree = tree;
            });
        });

    };

    $scope.$dismiss = function() {
        $modalInstance.dismiss();
    };

});
