/* Services */
angular.module('myApp.services').factory('selectExistingRuleModal', function($modal) {
    'use strict';

    return {
        select: function(usage) {
            var modalInstance = $modal.open({
                controller: 'selectExistingRuleModalCtrl',
                templateUrl: '/template/browse/rule/selectExistingRule',
                windowClass: 'windowHeight',
                resolve: {
                    referenceUsage: function() {
                        return usage;
                    }
                }
            });

            return modalInstance.result;
        }
    };
});
