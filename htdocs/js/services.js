'use strict';

/* Services */

/**
 * Simple service returning the version of the application
 */
angular.module('myApp.services', [])
    .value('version', '0.3');


/**
 * Questionnaire service
 */
angular.module('myApp.resourceServices', ['ngResource'])
    .factory('Questionnaire',function ($resource) {
        return $resource('/api/questionnaire/:id');
    })
    .factory('Survey',function ($resource) {
        return $resource('/api/survey/:id', {}, {
            create: {
                method: 'POST'
            },
            update: {
                method: 'PUT'
            }
        });
    })
    .factory('Question',function ($resource) {
        return $resource('/api/questionnaire/:idQuestionnaire/question/:id', {}, {
            update: {
                method: 'PUT'
            }
        });
    })
    .factory('Answer', function ($resource) {
        return $resource('/api/answer/:id', {}, {
            create: {
                method: 'POST'
            },
            update: {
                method: 'PUT'
            }
        });
    })
    .factory('ConfirmDelete', function ($rootScope) {
        // @todo rename me
        var foo = {
            test: function(row) {
                console.log(row);
            }
        }
        return foo;
    });