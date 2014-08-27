/* Services */
angular.module('myApp.services').factory('RuleWizardModal', function($modal) {
    'use strict';

    return {
        select: function() {
            var modalInstance = $modal.open({
                controller: 'RuleWizardModalCtrl',
                templateUrl: '/template/browse/rule/wizard'
            });
            return modalInstance.result;
        }
    };
});
