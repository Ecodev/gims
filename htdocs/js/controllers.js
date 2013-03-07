'use strict';

/* Controllers */


angular.module('myApp').controller('MyCtrl1', function () {
    
});

angular.module('myApp').controller('MyCtrl2', function () {
    
});

angular.module('myApp').controller('UserCtrl', function($scope) {

    $scope.open = function() {
        $scope.shouldBeOpen = true;
    };

    $scope.close = function() {
        $scope.closeMsg = 'I was closed at: ' + new Date();
        $scope.shouldBeOpen = false;
    };

    $scope.items = ['item1', 'item2'];

    $scope.opts = {
        backdropFade: true,
        dialogFade: true
    };
});
