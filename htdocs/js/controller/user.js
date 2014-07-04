angular.module('myApp').controller('UserCtrl', function($scope, $http, authService, $modal, $rootScope, requestNotification, $window, $analytics) {
    'use strict';
    $scope.getRequestCount = requestNotification.getRequestCount;

    function userLoggedIn(user) {
        if ($window.ga) {
            $window.ga('set', 'dimension1', user.id + ': ' + user.name);
            $analytics.eventTrack('logged', {category: 'login', label: 'logged'});
        }

        $scope.user = user;
        $rootScope.user = $scope.user;
        authService.loginConfirmed();
        $rootScope.$emit('gims-loginConfirmed', user);
    }

    // Intercept the event broadcasted by http-auth-interceptor when a request get a HTTP 401 response
    $scope.$on('event:auth-loginRequired', function() {
        $scope.promptLogin();
    });

    // Reload existing logged in user (eg: when refreshing page)
    $http.get('/user/login').success(function(data) {
        if (data.status == 'logged') {
            userLoggedIn(data);
        }
    });

    $scope.promptLogin = function() {

        var modalInstance = $modal.open({
            templateUrl: 'loginWindow.html',
            controller: 'LoginWindowCtrl'
        });

        modalInstance.result.then(userLoggedIn);
    };

});

angular.module('myApp').controller('LoginWindowCtrl', function($scope, $http, $modalInstance, $log, $rootScope) {
    'use strict';

    function resetErrors() {
        $scope.invalidUsernamePassword = false;
        $scope.userExisting = false;
    }
    resetErrors();

    $scope.cancelLogin = function()
    {
        $modalInstance.dismiss();
    };

    $scope.login = {};
    $scope.register = {};

    $scope.sendLogin = function() {
        $scope.signing = true;
        resetErrors();
        $http.post('/user/login', $scope.login).success(function(data) {
            $scope.signing = false;
            if (data.status == 'logged')
            {
                $scope.invalidUsernamePassword = false;
                $scope.user = data;
                $rootScope.user = $scope.user;
                $modalInstance.close(data);
            } else if (data.status == 'failed')
            {
                $scope.invalidUsernamePassword = true;
            }
        }).error(function(data) {
            $scope.signing = false;
            $log.error('Server error', data);
        });
    };

    $scope.sendRegister = function() {
        $scope.registering = true;
        resetErrors();
        $http.post('/user/register', $scope.register).success(function(data) {
            $scope.registering = false;

            // auto-login with the user we just created
            $modalInstance.close(data);
        }).error(function(data) {
            $scope.registering = false;

            if (data.message.email.recordFound) {
                $scope.userExisting = true;
            }
        });
    };

    $scope.toLowerCase = function() {
        if ($scope.login.identity) {
            $scope.login.identity = $scope.login.identity.toLowerCase();
        }

        if ($scope.register.email) {
            $scope.register.email = $scope.register.email.toLowerCase();
        }
    };
});
