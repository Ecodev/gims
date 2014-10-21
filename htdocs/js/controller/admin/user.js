/* Controllers */
angular.module('myApp').controller('Admin/User/CrudCtrl', function($scope, $routeParams, $location, Modal, Restangular) {
    'use strict';

    // Default redirect
    var returnUrl = '/admin/user';
    if ($routeParams.returnUrl) {
        returnUrl = $routeParams.returnUrl;
    }

    $scope.cancel = function() {
        $location.path(returnUrl).search('returnUrl', null);
    };

    $scope.actives = [
        {text: 'Yes', value: 'true'},
        {text: 'No', value: 'false'}
    ];

    $scope.saveAndClose = function() {
        this.save(returnUrl);
    };

    $scope.save = function(redirectTo) {
        $scope.sending = true;

        // First case is for update a user, second is for creating
        if ($scope.user.id) {
            $scope.user.put().then(function() {
                $scope.sending = false;

                if (redirectTo) {
                    $location.path(redirectTo);
                }
            }, function() {
                $scope.sending = false;
            });
        } else {
            Restangular.all('user').post($scope.user).then(function(user) {
                $scope.sending = false;

                if (!redirectTo) {
                    redirectTo = '/admin/user/edit/' + user.id;
                }
                $location.path(redirectTo);
            }, function() {
                $scope.sending = false;
            });
        }
    };

    // Delete a user
    $scope.delete = function() {
        Modal.confirmDelete($scope.user, {label: $scope.user.name, returnUrl: returnUrl});
    };

    // Load user if possible
    if ($routeParams.id) {
        Restangular.one('user', $routeParams.id).get({fields: 'metadata,phone,skype,job,ministry,address,zip,city,geoname'}).then(function(user) {
            $scope.user = user;
        });
    } else {
        $scope.user = {};
    }
});

/**
 * Admin User Controller
 */
angular.module('myApp').controller('Admin/UserCtrl', function($scope) {
    'use strict';

    // Configure gims-grid.
    $scope.gridOptions = {
        columnDefs: [
            {field: 'name', displayName: 'Name', width: '250px'},
            {field: 'email', displayName: 'Email', cellTemplate: '<div class="ngCellText" ng-class="col.colIndex()"><a href="mailto:{{row.entity[col.field]}}">{{row.entity[col.field]}}</a></div>'},
            {displayName: '', width: '70px', cellTemplate: '<a class="btn btn-default btn-xs" href="/admin/user/edit/{{row.entity.id}}" ><i class="fa fa-pencil fa-lg"></i></a><button type="button" class="btn btn-default btn-xs" ng-click="remove(row)" ><i class="fa fa-trash-o fa-lg"></i></button>'}
        ]
    };

});
