/**
 * Directive to resize the column when we want to show question labels
 * This allow us to avoid $watch()ing the property
 */
angular.module('myApp.directives').directive('gimsResizeForLabels', function($rootScope) {

    return {
        restrict: 'A',
        link: function(scope, element) {

            function setWidth() {
                element.css('width', scope.questionnaire.width);
            }

            // Initialize the width
            setWidth();

            // When toggled, change width accordingly
            $rootScope.$on('gims-tablefilter-show-labels-toggled', setWidth);
        }
    };
});
