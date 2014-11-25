/* Services */
angular.module('myApp.services').factory('RuleWizardModal', function($modal) {
    'use strict';

    return {
        select: function(rule) {
            var modalInstance = $modal.open({
                controller: 'RuleWizardModalCtrl',
                templateUrl: '/template/browse/rule/wizard',
                resolve: {
                    rule: function() {
                        return rule;
                    }
                }
            });
            return modalInstance.result;
        }
    };
});
