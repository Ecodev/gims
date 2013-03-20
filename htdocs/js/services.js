'use strict';

/* Services */


// Demonstrate how to register services
// In this case it is a simple value service.
angular.module('myApp.services', []).
    value('version', '0.1');

angular.module('myApp.answerService', ['ngResource']).
    factory('Answer', function ($resource) {
        return $resource('/api/answer/:id', {}, {
            query: {method: 'GET', params: {id: ''}, isArray: true},
            create: {method: 'PUT'},
            update: {method: 'PUT'}
        });
    })

angular.module('myApp.questionnaireService', ['ngResource']).
    factory('Questionnaire', function ($resource) {
        return $resource('/api/questionnaire/:id');
    })

// @todo try to merge me into answerService?
angular.module('myApp.questionnaireAnswerService', ['ngResource']).
    factory('QuestionnaireAnswer', function ($resource) {
        return $resource('/api/questionnaire/:idQuestionnaire/answer');
    })

angular.module('myApp.services.Answer', ['ngResource']).
    factory('Answer', function ($resource) {
        return $resource('/cakephp/demo_comments/:action/:id/:page/:limit:format',
            { id: '@id', 'page': '@page', 'limit': '@limit' },
            {
                'initialize': { method: 'GET', params: { action: 'initialize', format: '.json' }, isArray: true },
                'save': { method: 'POST', params: { action: 'create', format: '.json' } },
                'query': { method: 'GET', params: { action: 'read', format: '.json' }, isArray: true },
                'update': { method: 'PUT', params: { action: 'update', format: '.json' } },
                'remove': { method: 'DELETE', params: { action: 'delete', format: '.json' } }
            });
    })