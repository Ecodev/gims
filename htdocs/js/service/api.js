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
        .factory('CachedRestangular', function(Restangular) {
            'use strict';

            return Restangular.withConfig(function(RestangularConfigurer) {
                RestangularConfigurer.setDefaultHttpFields({cache: true});
            });
        })

        /**
         * Defines what questionnaire status are read-only
         */
        .value('questionnairesStatus', {
            validated: true,
            published: true,
            completed: true,
            rejected: true,
            'new': true
        });
