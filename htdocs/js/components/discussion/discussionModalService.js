/* Services */
angular.module('myApp.services').factory('DiscussionModal', function($modal) {
    'use strict';

    return {
        open: function(discussion) {
            var modalInstance = $modal.open({
                controller: 'DiscussionModalCtrl',
                templateUrl: '/template/browse/discussion/modal',
                resolve: {
                    discussion: function() {
                        return discussion;
                    }
                }
            });

            return modalInstance.result;
        }
    };
});
