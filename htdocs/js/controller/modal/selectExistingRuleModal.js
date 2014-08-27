/**
 * Controller for user editing within a modal
 */
angular.module('myApp').controller('selectExistingRuleModalCtrl', function($scope, $modalInstance, $timeout, referenceUsage) {
    'use strict';

    $scope.referenceUsage = referenceUsage;

    $scope.ruleFields = {fields: 'structure'};

    $scope.selectRule = function(rule) {
        $timeout(function() {
            $modalInstance.close(rule);
        });
    };

    $scope.$dismiss = function() {
        $modalInstance.dismiss();
    };

});
