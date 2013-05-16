angular.module('myApp').controller('MyCtrl1', function () {

});

angular.module('myApp').controller('UserCtrl', function ($scope, $location, $http) {
    'use strict';
    
    $scope.promptLogin = function () {
        $scope.showLogin = true;
        $scope.redirect = $location.absUrl();
    };

    $scope.cancelLogin = function () {
        $scope.showLogin = false;
    };

    $scope.opts = {
        backdropFade: true,
        dialogFade: true
    };

    $scope.login = {};
    $scope.register = {};

    // Keep current URL up to date, so we can login and come back to current page
    $scope.redirect = $location.absUrl();
    $scope.$on("$routeChangeSuccess", function (event, current, previous) {
        $scope.redirect = $location.absUrl();
    });

    $scope.sendLogin = function () {
        $http.post('/user/login', $scope.login).success(function (data) {
            console.log('Success', data);
            return false;
        }).error(function (data, status, headers) {
            console.log('Error', status);
            return false;
        });
    };

});

