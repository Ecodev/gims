angular.module('myApp.directives').directive('gimsSelectToken', function($rootScope) {
    'use strict';

    return {
        restrict: 'A', // Only usage possible is with attribute
        scope: {
            part: '=',
            questionnaire: '=',
            filter: '=?',
            rule: '=?'
        },
        link: function(scope, element) {

            element.on('click', function() {

                if (scope.questionnaire && scope.part && (scope.filter || scope.rule)) {
                    var token = "{";
                    if (scope.filter) {
                        token += "F#" + scope.filter.id;
                    } else if (scope.rule) {
                        token += "R#" + scope.rule.id;
                    }

                    token += ",Q#" + scope.questionnaire.id + ",P#" + scope.part.id + "}";

                    $rootScope.$emit('gims-rule-token-selected', token);
                }
            });
        }
    };
});
