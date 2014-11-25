angular.module('myApp.directives').directive('gimsRuleWizard', function($rootScope, RuleWizardModal) {
    'use strict';

    return {
        restrict: 'A', // Only usage possible is with attribute
        link: function(scope, element) {
            element.on('click', function() {
                RuleWizardModal.select(scope.rule).then(function(token) {
                    $rootScope.$emit('gims-rule-token-selected', token);
                    scope.$evalAsync();
                });
            });

        }
    };
});
