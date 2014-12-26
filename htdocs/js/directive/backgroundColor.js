/**
 * Color the rows of /browse/table/filter if the filter has a background color defined
 */
angular.module('myApp.directives').directive('backgroundColor', function() {
    'use strict';

    return {
        restrict: 'A',
        link: function(scope, element) {
            if (scope.row.entity.bgColor) {
                element.css('background-color', scope.row.entity.bgColor);
            }
        }
    };
});
