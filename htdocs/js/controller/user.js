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

    $http.get('/user/login').success(function(data) {
        if (data.status == 'logged')
        {
            $scope.user = data
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
    }

    $scope.sendLogin = function () {
        $http.post('/user/login', $scope.login).success(function (data) {
            if (data.status == 'logged')
            {
                $scope.invalidUsernamePassword = false;
                $scope.hideLogin();
                authService.loginConfirmed();
                $scope.user = data;
            }
            else if (data.status == 'failed')
            {
                $scope.invalidUsernamePassword = true;
            }
        }).error(function (data, status, headers) {
            console.log('Server error', data);
        });
    };

    $scope.sendRegister = function() {
        $http.post('/api/user', $scope.register).success(function (data, status) {
            // auto-login with the account we just created
            $scope.login.identity = $scope.register.email;
            $scope.login.credential = $scope.register.password;
            $scope.sendLogin();
        }).error(function (data, status, headers) {
            if (data.message.email.recordFound)
                $scope.userExisting = true;
        });
    }

});

