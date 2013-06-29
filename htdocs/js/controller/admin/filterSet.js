/* Controllers */

/**
 * Admin Survey Controller
 */
angular.module('myApp').controller('Admin/FilterSetCtrl', function ($scope, $location, Modal, Restangular) {
    "use strict";

    // Initialize
    $scope.filterSets = Restangular.all('filter-set').getList();

    // Keep track of the selected row.
    $scope.selectedRow = [];

    // Configure ng-grid.
    $scope.gridOptions = {
        plugins: [new ngGridFlexibleHeightPlugin({minHeight: 800})],
        data: 'filterSets',
        enableCellSelection: true,
        showFooter: false,
        selectedItems: $scope.selectedRow,
        filterOptions: {},
        multiSelect: false,
        columnDefs: [
            {field: 'name', displayName: 'Name', width: '750px'},
            {displayName: '', cellTemplate: '' +
                        '<button type="button" class="btn btn-mini" ng-click="remove(row)" ><i class="icon-trash icon-large"></i></button>'}
        ]
    };

    // <button type="button" class="btn btn-mini" ng-click="edit(row)" ><i class="icon-pencil icon-large"></i></button>
    $scope.remove = function (row) {
        Modal.confirmDelete(row.entity, {objects: $scope.filterSets, label: row.entity.code, returnUrl: '/admin/filter-set'});
    };

//    $scope.edit = function (row) {
//        $location.path('/admin/survey/edit/' + row.entity.id);
//    };

});