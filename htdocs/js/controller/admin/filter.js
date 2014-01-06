/* Controllers */

angular.module('myApp').controller('Admin/Filter/CrudCtrl', function ($scope, $location, $routeParams, Modal, Restangular) {
    "use strict";

    $scope.fields = {fields:'children,children.children,children.children.children,children.children.children.children,parents,parents.parents,parents.parents.parents,parents.parents.parents.parents'};

    var redirectTo = '/admin/filter';
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
        Restangular.one('filter', $routeParams.id).get($scope.fields).then(function (filter) {
            $scope.filter = filter;
        });

    } else {
        $scope.filter = {};
    }


    $scope.save = function (redirectTo) {
        $scope.sending = true;

        // First case is for update a question, second is for creating
        if ($scope.filter.id) {
            $scope.filter.put($scope.fields).then(function (filter) {
                $scope.sending = false;
                $scope.filter = filter;
                if (redirectTo) {
                    $location.path(redirectTo);
                }
            });
        }
        else {
            Restangular.all('filter').post($scope.filter).then(function (filter) {
                $scope.sending = false;
                if (!redirectTo) {
                    redirectTo = '/admin/filter/edit/' + filter.id;
                }
                $location.path(redirectTo);
            });
        }
    };

});















/**
 * Admin filter Controller
 */
angular.module('myApp').controller('Admin/FilterCtrl', function ($scope, $location, Modal, Restangular) {
    "use strict";

    // Initialize
    $scope.filters = Restangular.all('filter').getList();

    // Keep track of the selected row.
    $scope.selectedRow = [];

    // Configure ng-grid.
    $scope.gridOptions = {
        plugins: [new ngGridFlexibleHeightPlugin({minHeight: 800})],
        data: 'filters',
        enableCellSelection: true,
        showFooter: false,
        selectedItems: $scope.selectedRow,
        filterOptions: {},
        multiSelect: false,
        columnDefs: [
            {field: 'name', displayName: 'Name'},
            {displayName: '', width: '70px', cellTemplate: '' +
                '<a class="btn btn-default btn-xs" href="/admin/filter/edit/{{row.entity.id}}"><i class="fa fa-pencil fa-lg"></i></a>'+
                '<button type="button" class="btn btn-default btn-xs" ng-click="remove(row)" ><i class="fa fa-trash-o fa-lg"></i></button>'
            }
        ]
    };

    // <button type="button" class="btn btn-default btn-xs" ng-click="edit(row)" ><i class="fa fa-pencil fa-lg"></i></button>
    $scope.remove = function (row) {
        Modal.confirmDelete(row.entity, {objects: $scope.filters, label: row.entity.code, returnUrl: '/admin/filter'});
    };

    $scope.edit = function (row) {
        $location.path('/admin/filter-set/edit/' + row.entity.id);
    };

});