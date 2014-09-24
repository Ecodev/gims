/**
 * Directive to show a filter value with its menu and everything.
 * Highly optimized for performance, $watch() are avoided at all cost
 */
angular.module('myApp.directives').directive('gimsCell', function($rootScope, questionnairesStatus, TableFilter) {

    // A helper function to call $setValidity and return the value / undefined,
    // a pattern that is repeated a lot in the input validation logic.
    function validate(ctrl, validatorName, validity, value) {
        ctrl.$setValidity(validatorName, validity);
        return validity ? value : undefined;
    }

    /**
     * Set the value of a input (ng-model) before the value is changed
     * Used in function saveAnswer().
     * Avoid to do some ajax requests when we just blur field without changing value.
     * @param question
     * @param answer
     * @param part
     */
    function setAnswerInitialValue(question, answer, part) {
        answer.initialValue = answer[question.value];
        if (!answer.part && part) {
            answer.part = part;
        }
    }

    return {
        restrict: 'E',
        template:
                '<div class="input-group input-group-sm">' +
                '    <gims-cell-menu questionnaire="questionnaire" filter="filter" part="part"></gims-cell-menu>' +
                '    <input' +
                '        type="number"' +
                '        class="form-control text-center"' +
                '        placeholder="{{questionnaire.survey.questions[filter.id].filter.values[part.id].displayValue}}"' +
                '        ng-change="updateValidState()"' +
                '        ng-focus="focus()"' +
                '        ng-blur="blur()"' +
                '        ng-model="questionnaire.survey.questions[filter.id].answers[part.id].displayValue"' +
                '        ng-readonly="!questionnairesStatus[questionnaire.status] ||' +
                '            questionnaire.survey.questions[filter.id].answers[part.id].permissions.update === false ||' +
                '            data.mode.isSector && filter.level <= 1 ||' +
                '            !data.mode.isContribute"' +
                '    />' +
                '    <span class="input-group-addon"><i class="fa fa-fw">%</i></span>' +
                '</div>',
        replace: true,
        scope: {
            questionnaire: '=',
            filter: '=',
            part: '='
        },
        link: function(scope, element) {

            var input = element.find('input');
            var unitIcon = element.find('i');
            var inputController = input.controller('ngModel');

            scope.questionnairesStatus = questionnairesStatus;
            scope.data = TableFilter.getData();

            /**
             * When entering the input
             */
            scope.focus = function() {
                TableFilter.initQuestionAbsolute(scope.questionnaire.survey.questions[scope.filter.id]);
                TableFilter.getPermissions(scope.questionnaire.survey.questions[scope.filter.id], scope.questionnaire.survey.questions[scope.filter.id].answers[scope.part.id]);

                setAnswerInitialValue(scope.questionnaire.survey.questions[scope.filter.id], scope.questionnaire.survey.questions[scope.filter.id].answers[scope.part.id]);
                addValidators();
            };

            /**
             * When leaving the input
             */
            scope.blur = function() {
                TableFilter.saveAnswer(scope.questionnaire.survey.questions[scope.filter.id].answers[scope.part.id], scope.questionnaire.survey.questions[scope.filter.id], scope.filter, scope.questionnaire, scope.part);
                removeValidators();
            };

            /**
             * Manage CSS class manually, so we don't need to $watch() things that almost never change
             */
            scope.updateValidState = function() {
                if (inputController.$invalid) {
                    input.addClass('error');
                } else {
                    input.removeClass('error');
                }
            };

            var minValidator;
            var maxValidator;

            /**
             * Add validators only on demand
             */
            function addValidators() {
                // Add min validator
                var min = 0;
                minValidator = function(value) {
                    return validate(inputController, 'min', inputController.$isEmpty(value) || value >= min, value);
                };

                inputController.$parsers.push(minValidator);
                inputController.$formatters.push(minValidator);
                input.attr('min', min);

                // Add max validator
                var max = scope.questionnaire.survey.questions[scope.filter.id].max;
                maxValidator = function(value) {
                    return validate(inputController, 'max', inputController.$isEmpty(value) || value <= max, value);
                };

                inputController.$parsers.push(maxValidator);
                inputController.$formatters.push(maxValidator);
                input.attr('max', max);
            }

            /**
             * Remove validators added on the fly if any
             */
            function removeValidators() {

                // Remove existing validators
                _.remove(inputController.$parsers, function(a) {
                    return a === maxValidator || a === minValidator;
                });
                _.remove(inputController.$formatters, function(a) {
                    return a === maxValidator || a === minValidator;
                });

                minValidator = null;
                maxValidator = null;

                // Refresh the view
                inputController.$setValidity('min', true);
                inputController.$setValidity('max', true);
                inputController.$invalid = false;
                inputController.$render();
                scope.updateValidState();
            }

            /**
             * Apply custom visual style
             */
            function updateVisualStyle() {

                // Update absolute/percent icon
                if (scope.questionnaire.survey.questions[scope.filter.id].isAbsolute) {
                    unitIcon.text('');
                    unitIcon.addClass('fa-plus-square-o');
                } else {
                    unitIcon.text('%');
                    unitIcon.removeClass('fa-plus-square-o');
                }

                // Update if excluded from computing
                var isExcluded = scope.questionnaire.survey.questions[scope.filter.id].filter.values[scope.part.id].isExcludedFromComputing;
                if (isExcluded) {
                    input.addClass('excluded-from-computing');
                } else {
                    input.removeClass('excluded-from-computing');
                }
            }

            // When we get all data, apply custom visual style
            $rootScope.$on('gims-tablefilter-computed', updateVisualStyle);
        }
    };
});
