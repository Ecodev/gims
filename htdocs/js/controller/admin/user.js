/* Controllers */
angular.module('myApp').controller('Admin/User/CrudCtrl', function($scope, $routeParams, $location, Modal, Restangular, $http) {
    'use strict';

    // Default redirect
    var returnUrl = '/admin/user';
    if ($routeParams.returnUrl) {
        returnUrl = $routeParams.returnUrl;
    }

    if ($routeParams.firstLogin) {
        $scope.firstLogin = true;
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
        Restangular.one('user', $routeParams.id).get({fields: 'metadata,phone,skype,job,ministry,address,zip,city,geoname,gravatar'}).then(function(user) {
            $scope.user = user;
        });
    } else {
        $scope.user = {};
    }

    $scope.sendChangePassword = function() {
        $scope.changePasswordSent = false;
        $scope.changePasswordNotSent = false;

        $http.get('/user/change-password', {params: {email: $scope.user.email}}).success(function() {
            $scope.changePasswordSent = true;
        }).error(function() {
            $scope.changePasswordNotSent = true;
        });
    };

    $scope.tabs = [false, false, false, false];
    $scope.selectTab = function(tab) {
        $scope.tabs[tab] = true;
        $location.hash(tab);
    };

    // Set the tab from URL hash if any
    $scope.selectTab(parseInt($location.hash()));
});

/**
 * Admin User Controller
 */
angular.module('myApp').controller('Admin/UserCtrl', function($scope) {
    'use strict';

    $scope.queryParams = {fields: 'gravatar'};

    // Configure gims-grid.
    $scope.gridOptions = {
        columnDefs: [
            {field: 'gravatar', displayName: '', width: 25, cellTemplate: '<div class="ui-grid-cell-contents" ng-class="col.colIndex()"><img ng-src="{{row.entity.gravatar}}&s=20" /></div>'},
            {field: 'name', displayName: 'Name', width: 250},
            {field: 'email', displayName: 'Email', cellTemplate: '<div class="ui-grid-cell-contents" ng-class="col.colIndex()"><a href="mailto:{{row.entity[col.field]}}">{{row.entity[col.field]}}</a></div>'},
            {name: 'buttons', displayName: '', width: 70, cellTemplate: '<a class="btn btn-default btn-xs" href="/admin/user/edit/{{row.entity.id}}" ><i class="fa fa-pencil fa-lg"></i></a><button type="button" class="btn btn-default btn-xs" ng-click="getExternalScopes().remove(row)" ><i class="fa fa-trash-o fa-lg"></i></button>'}
        ]
    };

});
