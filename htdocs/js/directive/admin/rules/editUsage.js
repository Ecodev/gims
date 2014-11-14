/**
 * This directive is used to edit a usage/rule
 */
angular.module('myApp.directives').directive('gimsEditUsage', function($rootScope) {
    'use strict';

    return {
        restrict: 'A', // Only usage possible is with attribute
        scope: {
            gimsEditUsage: '='
        },
        link: function(scope, element) {
            element.on('click', function() {
                $rootScope.$emit('gims-rule-usage-selected', scope.gimsEditUsage);
                scope.$evalAsync();
            });
        }
    };
});
