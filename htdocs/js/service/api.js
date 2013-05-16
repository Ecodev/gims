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
    .factory('Questionnaire', function ($resource) {
        return $resource('/api/questionnaire/:id');
    })
    .factory('Survey', function ($resource) {
        return $resource('/api/survey/:id', {}, {
            create: {
                method: 'POST'
            },
            update: {
                method: 'PUT'
            }
        });
    })
    .factory('User', function ($resource) {
        return $resource('/api/user/:id', {}, {
            create: {
                method: 'POST'
            },
            update: {
                method: 'PUT'
            }
        });
    })
    .factory('Role', function ($resource) {
        return $resource('/api/role/:id', {}, {
            update: {
                method: 'PUT'
            }
        });
    })
    .factory('UserSurvey', function ($resource) {
        return $resource('/api/:parent/:idParent/user-survey/:id', {}, {
            create: {
                method: 'POST'
            },
            update: {
                method: 'PUT'
            }
        });
    })
    .factory('UserQuestionnaire', function ($resource) {
        return $resource('/api/:parent/:idParent/user-questionnaire/:id', {}, {
            create: {
                method: 'POST'
            },
            update: {
                method: 'PUT'
            }
        });
    })
    .factory('QuestionnaireQuestion', function ($resource) {
        return $resource('/api/questionnaire/:idQuestionnaire/question/:id', {}, {
            update: {
                method: 'PUT'
            }
        });
    })
    .factory('Question', function ($resource) {
        return $resource('/api/question/:id', {}, {
            create: {
                method: 'POST'
            },
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
    .factory('Country', function ($resource) {
        return $resource('/api/country/:id');
    })
    .factory('Part', function ($resource) {
        return $resource('/api/part/:id');
    })
    .factory('Filter', function ($resource) {
        return $resource('/api/filter/:id');
    })
    .factory('FilterSet', function ($resource) {
        return $resource('/api/filter-set/:id');
    });