/* Services */
angular.module('myApp.services').factory('FilterModal', function($modal) {
    'use strict';

    return {
        select: function(params) {
            var modalInstance = $modal.open({
                controller: 'FilterModalCtrl',
                templateUrl: '/js/components/filter/templates/filterModal.phtml',
                windowClass: 'windowHeight',
                resolve: {
                    params: function() {
                        return params;
                    }
                }
            });

            return modalInstance.result;
        }
    };

});
