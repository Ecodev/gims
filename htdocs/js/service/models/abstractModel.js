angular.module('myApp.services').factory('AbstractModel', function($q, Restangular) {
    'use strict';

    return {

        // edit
        put: function(object, type, params) {
            Restangular.restangularizeElement(null, object, type);
            object.put(params);
        },

        // add
        post: function(object, type, params) {
            return Restangular.all(type).post(object, params);
        },

        get: function(id, type, params) {
            Restangular.one(type, id).get(params);
        }
    };
});
