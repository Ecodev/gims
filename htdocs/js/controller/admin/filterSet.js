/* Controllers */

angular.module('myApp').controller('Admin/FilterSet/CrudCtrl', function ($scope, $location, $routeParams, Modal, Restangular) {
    "use strict";



    var redirectTo = '/admin/filter-set';
    if ($routeParams.returnUrl) {
        redirectTo = $routeParams.returnUrl;
    }

    $scope.saveAndClose = function () {
        this.save(redirectTo);
    };

    $scope.cancel = function () {
        $location.path(redirectTo).search('returnUrl', null).hash(null);
    };


    if ($routeParams.id) {
        Restangular.one('filter-set', $routeParams.id).get({fields:'filters'}).then(function (filterSet) {
            $scope.filterSet = filterSet;
        });

    } else {
        $scope.filterSet = {};
    }


    $scope.save = function (redirectTo) {
        $scope.sending = true;

        // First case is for update a question, second is for creating
        if ($scope.filterSet.id) {
            $scope.filterSet.put({fields:'filters'}).then(function (filterSet) {
                $scope.sending = false;
                $scope.filterSet = filterSet;
                if (redirectTo) {
                    $location.path(redirectTo);
                }
            });
        }
        else {
            Restangular.all('filter-set').post($scope.filterSet).then(function (filterSet) {
                $scope.sending = false;
                if (!redirectTo) {
                    redirectTo = '/admin/filter-set/edit/' + filterSet.id;
                }
                $location.path(redirectTo);
            });
        }
    };

});






/**
 * Admin filterset Controller
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
            {field: 'name', displayName: 'Name'},
            {displayName: '', width: '70px', cellTemplate: '' +
                '<div class="btn-group">'+
                '   <a class="btn btn-default btn-xs" href="/admin/filter-set/edit/{{row.entity.id}}"><i class="fa fa-pencil fa-lg"></i></a>'+
                '   <button type="button" class="btn btn-default btn-xs" ng-click="remove(row)" ><i class="fa fa-trash-o fa-lg"></i></button>'+
                '</div>'
            }
        ]
    };

    // <button type="button" class="btn btn-default btn-xs" ng-click="edit(row)" ><i class="fa fa-pencil fa-lg"></i></button>
    $scope.remove = function (row) {
        Modal.confirmDelete(row.entity, {objects: $scope.filterSets, label: row.entity.code, returnUrl: '/admin/filter-set'});
    };

    $scope.edit = function (row) {
        $location.path('/admin/filter-set/edit/' + row.entity.id);
    };

});