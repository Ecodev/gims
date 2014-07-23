angular.module('myApp.directives').directive('gimsRuleWizard', function($rootScope) {
    'use strict';

    return {
        restrict: 'A', // Only usage possible is with attribute
        scope: {},
        link: function(scope, element) {
            element.on('click', function() {
                scope.showWizardModal();
            });
        },
        controller: function($scope, RuleWizardModal) {
            $scope.showWizardModal = function() {
                RuleWizardModal.select().then(function(token) {
                    $rootScope.$emit('gims-rule-token-selected', token);
                });
            };
        }
    };
});
