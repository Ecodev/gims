angular.module('myApp.services').factory('Utility', function() {
    'use strict';

    return {

        /**
         * Allow to remove all attributes to an object without creating a new one (preserves reference)
         * @param object
         */
        resetObject: function(object) {
            if (_.isObject(object)) {
                for (var key in object) {
                    delete (object[key]);
                }
            }
        }
    };
});
