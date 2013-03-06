'use strict';

/* Controllers */


function MyCtrl1() {
}
MyCtrl1.$inject = [];


function MyCtrl2() {
}
MyCtrl2.$inject = [];


var UserCtrl = function($scope) {

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
};
