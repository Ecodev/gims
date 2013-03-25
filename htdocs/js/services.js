'use strict';

/* Services */

/**
 * Simple service returning the version of the application
 */
angular.module('myApp.services', []).
    value('version', '0.3');


/**
 * Questionnaire service
 */
angular.module('myApp.questionnaireService', ['ngResource']).
    factory('questionnaireService', function ($resource) {
        return $resource('/api/questionnaire/:id');
    });

/**
 * Questionnaire service
 */
angular.module('myApp.questionService', ['ngResource']).
    factory('questionService', function ($resource) {
        return $resource('/api/questionnaire/:idQuestionnaire/question');
    });

/**
 * Answer service
 */
angular.module('myApp.answerService', ['ngResource']).
    factory('answerService', function ($resource) {
        var resource1, resource2;

        // Define resource with first possible route
        resource1 = $resource('/api/questionnaire/:idQuestionnaire/answer/:id', {}, {
            query: {
                method: 'GET',
                params: {id: ''},
                isArray: true
            }
        });

        // Define resource with second possible route.
        resource2 = $resource('/api/answer/:id', {}, {
            create: {
                method: 'POST'
            },
            update: {
                method: 'PUT'
            }
        });

        // Overwrite method
        resource1.update = resource2.update.bind(null);
        resource1.get = resource2.get.bind(null);
        return resource1;
    });
