angular.module('myApp').controller('UserCtrl', function ($scope, $location, $http, authService) {
    'use strict';

    // Intercept the event broadcasted by http-auth-interceptor when a request get a HTTP 401 response
    $scope.$on('event:auth-loginRequired', function() {
        $scope.promptLogin();
    });

    // TODO: replace with actual logged in user ID
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
    };

    $scope.hideLogin = function() {
        $scope.showLogin = false;
    }

    $scope.opts = {
        backdropFade: true,
        dialogFade: true
    };

    $scope.$on("$routeChangeSuccess", function (event, current, previous) {
        $scope.redirect = $location.absUrl();
    });

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
        $http.post('/user/register', $scope.register).success(function (data) {
            
        });
    }

});

