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
        },

        /**
         * Return a parameter in a list of objects (or single object)
         * Almost could use _.pluck but the loop in pluck dont use angular.foreach and uses some angular function as object. pluck can't neither differentiate if server returns list or single object
         * @param data
         * @param attribute
         * @param permission
         * @returns {Array}
         */
        getAttribute: function(data, attribute, permission) {
            var list = [];
            if (_.isArray(data)) {
                angular.forEach(data, function(obj) {
                    list = list.concat(obj[attribute]);
                });
            } else if (data) {
                list = data[attribute];
            }

            list = _.filter(list, function(l) {
                if (!permission || permission && l && (!l.permissions || l.permissions && l.permissions[permission])) {
                    return true;
                }
                return false;
            });

            return list;
        }
    };
});
