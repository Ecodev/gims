angular.module('myApp.directives').directive('backgroundColor', function() {
    'use strict';

    return {
        restrict: 'A',
        link: function(scope, element) {
            if (scope.filter.bgColor) {
                element.css("background-color", scope.filter.bgColor);
            }
        }
    };
});
