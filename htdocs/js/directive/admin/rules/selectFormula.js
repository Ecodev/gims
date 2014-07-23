angular.module('myApp.directives').directive('gimsSelectFormula', function($rootScope) {
    'use strict';

    return {
        restrict: 'A', // Only usage possible is with attribute
        scope: {
            rule: '='
        },
        link: function(scope, element) {
            element.on('click', function() {
                $rootScope.$emit('gims-rule-selected', scope.rule);
            });
        }
    };
});
