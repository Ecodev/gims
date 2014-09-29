/* Controllers */

angular.module('myApp').controller('Admin/FilterSet/CrudCtrl', function($scope, $location, $routeParams, Restangular, Modal) {
    "use strict";

    $scope.fields = {fields: 'filters,filters.paths,filters.children,filters.color,filter.genericColor'};
    $scope.tabs = [false, false];
    $scope.selectTab = function(tab) {
        $scope.tabs[tab] = true;
        $location.hash(tab);
    };

    // Set the tab from URL hash if any
    $scope.selectTab(parseInt($location.hash()));

    var returnUrl = '/admin/filter-set';
    if ($routeParams.returnUrl) {
        returnUrl = $routeParams.returnUrl;
    }

    $scope.saveAndClose = function() {
        this.save(returnUrl);
    };

    $scope.cancel = function() {
        $location.path(returnUrl).search('returnUrl', null).hash(null);
    };

    if ($routeParams.id) {
        Restangular.one('filter-set', $routeParams.id).get($scope.fields).then(function(filterSet) {
            $scope.filterSet = filterSet;
        });

    } else {
        $scope.filterSet = {};
    }

    $scope.save = function(redirectTo) {
        $scope.sending = true;

        // First case is for update a question, second is for creating
        if ($scope.filterSet.id) {
            $scope.filterSet.put({fields: 'filters'}).then(function(filterSet) {
                $scope.sending = false;
                $scope.filterSet = filterSet;
                if (redirectTo) {
                    $location.path(redirectTo);
                }
            }, function() {
                $scope.sending = false;
            });
        } else {
            Restangular.all('filter-set').post($scope.filterSet).then(function(filterSet) {
                $scope.sending = false;
                if (!redirectTo) {
                    redirectTo = '/admin/filter-set/edit/' + filterSet.id;
                }
                $location.path(redirectTo);
            }, function() {
                $scope.sending = false;
            });
        }
    };

    // Delete a FilterSet
    $scope.delete = function() {
        Modal.confirmDelete($scope.filterSet, {returnUrl: returnUrl});
    };
});

/**
 * Admin filterset Controller
 */
angular.module('myApp').controller('Admin/FilterSetCtrl', function($scope) {
    "use strict";

    // Configure gims-grid.
    $scope.gridOptions = {
        columnDefs: [
            {field: 'name', displayName: 'Name'},
            {
                displayName: '',
                width: '70px',
                cellTemplate: '<div class="btn-group">' +
                        '   <a class="btn btn-default btn-xs" href="/admin/filter-set/edit/{{row.entity.id}}"><i class="fa fa-pencil fa-lg"></i></a>' +
                        '   <button type="button" class="btn btn-default btn-xs" ng-click="remove(row)" ><i class="fa fa-trash-o fa-lg"></i></button>' +
                        '</div>'
            }
        ]
    };

});
