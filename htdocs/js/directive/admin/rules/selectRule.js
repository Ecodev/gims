angular.module('myApp.directives').directive('gimsSelectRule', function($rootScope) {
    'use strict';

    return {
        restrict: 'A', // Only usage possible is with attribute
        scope: {
            gimsSelectRule: '='
        },
        link: function(scope, element) {

            element.on('click', function() {
                $rootScope.$emit('gims-rule-selected', scope.gimsSelectRule);
            });
        }
    };
});
