/**
 * Show a calendar button which will open the datepicker for the sibling date input
 */
angular.module('myApp.directives').directive('gimsDatepickerButton', function() {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with element
        template: '<button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>',
        link: function($scope, $element) {
            var dateInput = $element.siblings('[is-open]');
            var openVariable = dateInput.attr('is-open');

            $element.click(function(event) {
                event.preventDefault();
                event.stopPropagation();
                $scope[openVariable] = true;
                $scope.$evalAsync();
            });
        }
    };
});
