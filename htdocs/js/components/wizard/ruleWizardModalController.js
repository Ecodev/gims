/**
 * Controller to compose formula syntax
 */
angular.module('myApp').controller('RuleWizardModalCtrl', function($scope, $modalInstance, $timeout, rule, RuleSuggestor) {
    'use strict';

    $scope.currentValue = {
        id: 'current',
        name: 'Current'
    };

    $scope.token = {
        filter: $scope.currentValue,
        questionnaire: $scope.currentValue,
        part: $scope.currentValue,
        year: 0
    };

    $scope.availableTokens = [
        {
            group: 'Before regression',
            name: 'Filter value',
            description: "Reference a filter value.",
            filter: true,
            questionnaire: true,
            part: true,
            rule: false,
            step: true,
            year: false,
            toString: function(config) {
                return '{F#' + config.filter.id + ',Q#' + config.questionnaire.id + ',P#' + config.part.id + (config.step ? ',S#2' : '') + '}';
            }
        },
        {
            group: 'Before regression',
            name: 'Question label',
            description: 'Reference a question label. If the question has no answer, it will return NULL. When used with ISTEXT(), it can be used to detect if an answer exists.',
            filter: true,
            questionnaire: true,
            part: false,
            rule: false,
            step: false,
            year: false,
            toString: function(config) {
                return '{F#' + config.filter.id + ',Q#' + config.questionnaire.id + '}';
            }
        },
        {
            group: 'Before regression',
            name: 'Rule value (Calculations/Estimations/Ratios)',
            description: 'Reference a rule value. Typically used to reference a Calculation, Estimation or Ratio.',
            filter: false,
            questionnaire: false,
            part: false,
            rule: false,
            step: false,
            year: false,
            questionnaireUsage: true,
            toString: function(config) {
                return '{R#' + config.usage.rule.id + ',Q#' + config.usage.questionnaire.id + ',P#' + config.usage.part.id + '}';
            }
        },
        {
            group: 'Before regression',
            name: 'Population value',
            description: 'Reference the population data of the questionnaire\'s country. This is an absolute value expressed in number of persons.',
            filter: false,
            questionnaire: true,
            part: true,
            rule: false,
            step: false,
            year: false,
            toString: function(config) {
                return '{Q#' + config.questionnaire.id + ',P#' + config.part.id + '}';
            }
        },
        {
            group: 'After regression',
            name: 'Cumulated population',
            description: 'Reference the cumulated population for all current questionnaires for the specified part.',
            filter: false,
            questionnaire: false,
            part: true,
            rule: false,
            step: false,
            year: false,
            toString: function(config) {
                return '{Q#all,P#' + config.part.id + '}';
            }
        },
        {
            group: 'After regression',
            name: 'Current year',
            description: 'Reference the year we are currently computing. This may be useful for very exceptional edge cases, but should be avoided as much as possible.',
            filter: false,
            questionnaire: false,
            part: false,
            rule: false,
            step: false,
            year: false,
            toString: function() {
                return '{Y}';
            }
        },
        {
            group: 'Both',
            name: 'List of all filter values',
            description: 'Reference a list of available filter values for all questionnaires. The result use Excel array constant syntax (eg: "{1,2,3}"). This should be used with Excel functions such as COUNT() and AVERAGE().',
            filter: true,
            questionnaire: false,
            part: false,
            rule: false,
            step: false,
            year: false,
            toString: function(config) {
                return '{F#' + config.filter.id + ',Q#all}';
            }
        },
        {
            group: 'Both',
            name: 'Filter value after regression',
            description: 'Reference a Filter regression value for a specific part and year. The year is defined by the year currently being computed plus a user-defined offset. To express "1 year earlier" the offset would be -1, and for "3 years later", it would be +3. To stay on the same year, use an offset of 0.',
            filter: true,
            questionnaire: false,
            part: true,
            rule: false,
            step: false,
            year: true,
            toString: function(config) {
                var year = config.year > 0 ? '+' + config.year : config.year;
                return '{F#' + config.filter.id + ',P#' + config.part.id + ',Y' + year + '}';
            }
        },
        {
            group: 'Both',
            name: 'Value if this rule is ignored',
            description: 'Reference the value if computed without this rule. It allows to conditionally apply a rule with syntaxes such as "IF(can_apply_my_rule, compute_some_result, {self})".',
            filter: false,
            questionnaire: false,
            part: false,
            rule: false,
            step: false,
            year: false,
            toString: function() {
                return '{self}';
            }
        }
    ];

    $scope.tokenCanBeInserted = function() {

        if (!$scope.token.selected) {
            return false;
        }

        if ($scope.token.selected.rule && !$scope.token.rule) {
            return false;
        }

        if ($scope.token.selected.questionnaireUsage && !$scope.token.usage) {
            return false;
        }

        return true;
    };

    $scope.insertToken = function() {
        $timeout(function() {
            var tokenString = $scope.token.selected.toString($scope.token);
            $modalInstance.close(tokenString);
        });
    };

    $scope.$dismiss = function() {
        $modalInstance.dismiss();
    };

    // Take care of questionnaireUsages selection
    $scope.suggestedQuestionnaires = RuleSuggestor.getSuggestedQuestionnaires(rule);
    $scope.$watch('[token.selected.questionnaireUsage, token.questionnaire]', function() {
        if ($scope.token.selected && $scope.token.selected.questionnaireUsage) {

            // Prevent selection of current value
            if ($scope.token.questionnaire == $scope.currentValue) {
                $scope.token.questionnaire = null;
            }

            if ($scope.token.questionnaire) {
                $scope.suggestedQuestionnaireUsages = RuleSuggestor.getSuggestedQuestionnaireUsages(rule, $scope.token.questionnaire);
            }

            $scope.token.usage = null;
        }
    }, true);

});
