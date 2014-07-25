angular.module('myApp.services').factory('AbstractModel', function($q, Restangular) {
    'use strict';

    return {

        // edit
        put: function(object, type, params) {
            var deferred = $q.defer();
            Restangular.restangularizeElement(null, object, type);
            object.put(params).then(function(object) {
                deferred.resolve(object);
            }, function(data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },

        // add
        post: function(object, type, params) {
            var deferred = $q.defer();
            Restangular.all(type).post(object, params).then(function(object) {
                deferred.resolve(object);
            }, function(data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },

        remove: function(object, type) {
            var deferred = $q.defer();
            Restangular.restangularizeElement(null, object, type);
            object.remove().then(function() {
                deferred.resolve();
            }, function(data) {
                deferred.reject(data);
            });
            return deferred.promise;
        },

        get: function(id, type, params) {
            var deferred = $q.defer();
            Restangular.one(type, id).get(params).then(function(object) {
                deferred.resolve(object);
            }, function(data) {
                deferred.reject(data);
            });
            return deferred.promise;
        }
    };
});
