/**
 * This directive is used to create a new usage (and rule)
 */
angular.module('myApp.directives').directive('gimsAddUsage', function($rootScope) {
    'use strict';

    return {
        restrict: 'A', // Only usage possible is with attribute
        scope: {
            questionnaire: '=',
            filter: '=',
            part: '='
        },
        link: function(scope, element) {

            element.on('click', function() {
                var params;
                if (scope.questionnaire.id && scope.filter && scope.filter.id && scope.part.id) {
                    params = {
                        questionnaire: scope.questionnaire,
                        filter: scope.filter,
                        part: scope.part
                    };
                } else if (scope.questionnaire.id && !scope.filter && scope.part.id) {
                    params = {
                        questionnaire: scope.questionnaire,
                        part: scope.part
                    };
                }

                if (params) {
                    $rootScope.$emit('gims-rule-usage-added', params);
                    scope.$evalAsync();
                }
            });
        }
    };
});
