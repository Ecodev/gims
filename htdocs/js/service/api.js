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
        });
