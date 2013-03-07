'use strict';

/* Controllers */


angular.module('myApp').controller('MyCtrl1', function() {

});

angular.module('myApp').controller('MyCtrl2', function() {

});

angular.module('myApp').controller('UserCtrl', function($scope, $location) {

    $scope.promptLogin = function() {
        $scope.showLogin = true;
        $scope.redirect = $location.absUrl();
    };

    $scope.cancelLogin = function() {
        $scope.showLogin = false;
    };

    $scope.promptRegister = function() {
        $scope.showRegister = true;
        $scope.redirect = $location.absUrl();
    };

    $scope.cancelRegister = function() {
        $scope.showRegister = false;
    };

    $scope.opts = {
        backdropFade: true,
        dialogFade: true
    };

    // Keep current URL up to date, so we can login and come back to current page
    $scope.redirect = $location.absUrl();
    $scope.$on("$routeChangeSuccess", function(event, current, previous) {
        $scope.redirect = $location.absUrl();
    });
});
