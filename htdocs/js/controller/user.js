angular.module('myApp').controller('UserCtrl', function ($scope, $http, authService) {
    'use strict';

    // Intercept the event broadcasted by http-auth-interceptor when a request get a HTTP 401 response
    $scope.$on('event:auth-loginRequired', function() {
        $scope.promptLogin();
    });

    $scope.showLogin = false;
    $scope.opts = {
        backdropFade: true,
        dialogFade: true
    };

    // Reload existing logged in user (eg: when refreshing page)
    $http.get('/user/login').success(function(data) {
        if (data.status == 'logged')
        {
            $scope.user = data;
        }
    });

    $scope.promptLogin = function () {
        $scope.showLogin = true;
        $scope.login = {};
        $scope.register = {};
        $scope.invalidUsernamePassword = false;
        $scope.userExisting = false;

    };

    $scope.hideLogin = function() {
        $scope.showLogin = false;
    };

    $scope.sendLogin = function () {
        $scope.signing = true;
        $http.post('/user/login', $scope.login).success(function (data) {
            $scope.signing = false;
            if (data.status == 'logged')
            {
                $scope.invalidUsernamePassword = false;
                $scope.user = data;
                $scope.hideLogin();
                authService.loginConfirmed();
            }
            else if (data.status == 'failed')
            {
                $scope.invalidUsernamePassword = true;
            }
        }).error(function (data, status, headers) {
            $scope.signing = false;
            console.log('Server error', data);
        });
    };

    $scope.sendRegister = function() {
        $scope.registering = true;
        $http.post('/user/register', $scope.register).success(function (data, status) {
            $scope.registering = false;

            // auto-login with the account we just created
            $scope.user = data;
            $scope.hideLogin();
            authService.loginConfirmed();
        }).error(function (data, status, headers) {
            $scope.registering = false;
            if (data.message.email.recordFound)
                $scope.userExisting = true;
        });
    };

});

