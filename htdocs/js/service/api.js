'use strict';

/* Services */

/**
 * Simple service returning the version of the application
 */
angular.module('myApp.services')
    .value('version', '0.3');

/**
 * Resource service
 */
angular.module('myApp.services')
    .factory('Answer', function ($resource) {
        return $resource('/api/answer/:id', {}, {
            create: {
                method: 'POST'
            },
            update: {
                method: 'PUT'
            }
        });
    });