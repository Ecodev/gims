/**
 * Directive to resize the element when we want to show question labels
 * This allow us to avoid $watch()ing the property
 */
angular.module('myApp.directives').directive('gimsResizeForLabels', function($rootScope) {

    return {
        restrict: 'A',
        link: function(scope, element) {

            // When toggled, change class accordingly
            $rootScope.$on('gims-tablefilter-show-labels-toggled', function() {

                if (scope.questionnaire.showLabels) {
                    element.removeClass('col-md-12');
                    element.addClass('col-md-4');
                } else {
                    element.removeClass('col-md-4');
                    element.addClass('col-md-12');
                }
            });
        }
    };
});
