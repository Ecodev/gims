/**
 * This directive is used to insert a reference to the cell in a formula as token
 */
angular.module('myApp.directives').directive('gimsInsertToken', function($rootScope) {
    'use strict';

    return {
        restrict: 'A', // Only usage possible is with attribute
        link: function(scope, element) {

            var filterId = scope.$eval('filter.id');
            var questionnaireId = scope.$eval('questionnaire.id');
            var partId = scope.$eval('part.id');
            var ruleId = scope.$eval('rule.values[questionnaire.id][part.id].id');

            element.on('click', function() {

                if (questionnaireId && partId && (filterId || ruleId)) {
                    var token = "{";
                    if (filterId) {
                        token += "F#" + filterId;
                    } else if (ruleId) {
                        token += "R#" + ruleId;
                    }

                    token += ",Q#" + questionnaireId + ",P#" + partId + "}";

                    $rootScope.$emit('gims-rule-token-selected', token);
                    scope.$evalAsync();
                }
            });

            /**
             * Apply or reset highlight color if the token match at least one element in structure
             * @param {array} structure
             * @returns {undefined}
             */
            function updateHighlight(structure) {
                var match = _.find(structure, function(s) {
                    if (s.type == 'filterValue' && filterId == s.filter.id && questionnaireId == s.questionnaire.id && partId == s.part.id) {
                        return true;
                    } else if (s.type == 'ruleValue' && ruleId == s.rule.id && questionnaireId == s.questionnaire.id && partId == s.part.id) {
                        return true;
                    }
                });

                // If we match, apply highlight, otherwise, reset it
                var cssValue = '';
                if (match) {
                    cssValue = '4px solid ' + match.highlightColor;
                }

                element.css('outline', cssValue);
            }

            // Upon creation register ourself, to be notified of updates
            $rootScope.$emit('gims-rule-token-register', updateHighlight);

            // Upon destruction, unregister
            scope.$on('$destroy', function() {
                $rootScope.$emit('gims-rule-token-unregister', updateHighlight);
            });

        }
    };
});
