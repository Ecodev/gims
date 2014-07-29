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
                if (scope.questionnaire.id && scope.filter.id && scope.part.id) {
                    var params = {
                        questionnaire: scope.questionnaire,
                        filter: scope.filter,
                        part: scope.part
                    };
                    $rootScope.$emit('gims-rule-usage-added', params);
                }
            });
        }
    };
});
