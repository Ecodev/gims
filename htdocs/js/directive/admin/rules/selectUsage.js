angular.module('myApp.directives').directive('gimsSelectUsage', function($rootScope) {
    'use strict';

    return {
        restrict: 'A', // Only usage possible is with attribute
        scope: {
            gimsSelectUsage: '='
        },
        link: function(scope, element) {
            element.on('click', function() {
                $rootScope.$emit('gims-rule-usage-selected', scope.gimsSelectUsage);
            });
        }
    };
});
