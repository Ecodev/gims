/**
 * Directive to show AJAX errors to end-user as error message
 */
angular.module('myApp.directives').directive('gimsErrors', function(requestNotification) {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with element
        template: '<div class="container">' +
                '<div ng-repeat="error in errors" class="alert alert-danger ng-trans ng-trans-fade-up">' +
                '<button type="button" class="close" data-dismiss="alert" aria-hidden="true" ng-click="dismiss($index)">&times;</button>' +
                '<strong>{{error.data.title || "Oops"}}!</strong> ' +
                '<span ng-if="error.data.detail && error.data.title != \'Object is not valid\'">{{error.data.detail}}</span>' +
                '<ul ng-if="error.data.messages">' +
                '<li ng-repeat="message in error.data.messages">{{message}}</li>' +
                '</ul>' +
                '<span ng-if="!error.data.detail">Something went wrong ({{error.config.method}} {{error.config.url}} {{error.status}}), <a ng-click="reload()" href="">try reloading the page</a>.</span>' +
                '</div>' +
                '<div/>',
        // The linking function will add behavior to the template
        link: function() {
        },
        controller: function($scope) {
            $scope.dismiss = function(index) {
                $scope.errors.splice(index, 1);
            };

            // Reload the current page, without using the cache
            $scope.reload = function() {
                document.location.reload(true);
            };

            requestNotification.subscribeOnResponseError(function(response) {
                if (response.status !== 0 && response.status != 401) {
                    $scope.errors.push(response);

                }
            });

            $scope.errors = [];
        }
    };
});
