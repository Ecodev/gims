/**
 * Very simple controller for modal to allow easy close() and dimsiss().
 * This will be deletable once we upgrade to ui-boostrap 0.7+ (because it will provide same thing)
 */
angular.module('myApp').controller('SimpleModalCtrl', function($scope, $modalInstance) {
    'use strict';

    $scope.$close = function()
    {
        $modalInstance.close();
    };

    $scope.$dismiss = function()
    {
        $modalInstance.dismiss();
    };
});
