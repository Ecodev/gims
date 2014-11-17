/* Controllers */

angular.module('myApp').controller('Admin/Filter/CrudCtrl', function($scope, $location, $routeParams, Modal, Restangular) {
    "use strict";

    //$scope.fields = {fields: 'filterSets,children,children.paths,children.color,parents,parents.paths,parents.color,summands,summands.paths,summands.color,paths,color,bgColor'};
    $scope.fields = {fields: 'filterSets,children,children.bgColor,children.color,parents,parents.bgColor,parents.color,summands,summands.bgColor,summands.color,color,bgColor'};

    var returnUrl = '/admin/filter';
    if ($routeParams.returnUrl) {
        returnUrl = $routeParams.returnUrl;
    }

    $scope.saveAndClose = function() {
        this.save(returnUrl);
    };

    $scope.cancel = function() {
        $location.url(returnUrl);
    };

    if ($routeParams.id) {
        Restangular.one('filter', $routeParams.id).get($scope.fields).then(function(filter) {
            $scope.filter = filter;
        });

    } else {
        $scope.filter = {};
    }

    $scope.save = function(redirectTo) {
        $scope.sending = true;

        // First case is for update, second is for creating
        if ($scope.filter.id) {
            $scope.filter.put($scope.fields).then(function(filter) {
                $scope.sending = false;
                $scope.filter = filter;
                if (redirectTo) {
                    $location.path(redirectTo);
                }
            }, function() {
                $scope.sending = false;
            });
        } else {
            Restangular.all('filter').post($scope.filter).then(function(filter) {
                $scope.sending = false;
                if (!redirectTo) {
                    redirectTo = '/admin/filter/edit/' + filter.id;
                }
                $location.path(redirectTo);
            }, function() {
                $scope.sending = false;
            });
        }
    };

    // Delete a Filter
    $scope.delete = function() {
        Modal.confirmDelete($scope.filter, {returnUrl: returnUrl});
    };
});

/**
 * Admin filter Controller
 */
angular.module('myApp').controller('Admin/FilterCtrl', function($scope, $location) {
    "use strict";

    $scope.$watch('selectedFilter', function(selectedFilter)
    {
        if (selectedFilter) {
            $location.path('/admin/filter/edit/' + selectedFilter.id);
        }
    });

    // Configure gims-grid
    $scope.queryparams = {fields: 'color',flatten: true};
    $scope.gridOptions = {
        columnDefs: [
            {
                field: 'name',
                displayName: 'Name',
                cellTemplate: '<div class="ngCellText" ng-class="col.colIndex()">' +
                        '   <span style="padding-left: {{row.entity.level * 2}}em;">' +
                        '       <span style="display:inline-block;vertical-align:middle;"><i class="fa fa-gims-filter" style="color:{{row.entity.color}}"></i></span>' +
                        '       <span style="display:inline-block;vertical-align:middle;">{{row.entity.name}}</span>' +
                        '   </span>' +
                        '</div>'
            },
            {
                displayName: '',
                width: '70px',
                cellTemplate: '<div class="btn-group" style="margin:4px 0 0 4px;">' +
                        '   <a class="btn btn-default btn-xs" href="/admin/filter/edit/{{row.entity.id}}"><i class="fa fa-pencil fa-lg"></i></a>' +
                        '   <button type="button" class="btn btn-default btn-xs" ng-click="remove(row)" ><i class="fa fa-trash-o fa-lg"></i></button>' +
                        '</div>'
            }
        ]
    };

});
