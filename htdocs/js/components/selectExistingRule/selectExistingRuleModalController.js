/**
 * Controller for user editing within a modal
 */
angular.module('myApp').controller('selectExistingRuleModalCtrl', function($scope, $modalInstance, $timeout, referenceUsage, RuleSuggestor) {
    'use strict';

    $scope.ruleFields = {fields: 'structure'};
    $scope.suggestedRules = RuleSuggestor.getSuggestedRules(referenceUsage);

    $scope.opened = true;
    $scope.selection = {};
    $scope.setRule = function(rule) {
        $scope.selection.rule = rule;
    };

    $scope.selectRule = function(rule) {
        $timeout(function() {
            $modalInstance.close(rule);
        });
    };

    $scope.$dismiss = function() {
        $modalInstance.dismiss();
    };
});
