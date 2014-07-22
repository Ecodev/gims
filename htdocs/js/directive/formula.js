/**
 * Display a formula as an easy to read version (based on its structure)
 * <gims-formula structure="rule.structure" />
 */
angular.module('myApp.directives').directive('gimsFormula', function() {
    'use strict';
    return {
        restrict: 'E', // Only usage possible is with element
        template:
                '<span ng-repeat="s in structure" class="formula">' +
                '<span ng-switch="s.type">' +
                '<span class="text"  ng-switch-when="text"> {{s.content}} </span>' +
                '<span class="token" ng-switch-when="self">Ignore this rule</span>' +
                '<span class="token" ng-switch-when="filterValue" tooltip="Filter: {{s.filter}}, Questionnaire: {{s.questionnaire}}, Part: {{s.part}}"><i class="fa fa-gims-filter" style="color: {{s.color}};"></i> {{s.filter}}<span ng-if="s.questionnaire != \'current\'">, {{s.questionnaire}}</span><span ng-if="s.part != \'current\'">, {{s.part}}</span></span>' +
                '<span class="token" ng-switch-when="populationValue" tooltip="Questionnaire: {{s.questionnaire}}, Part: {{s.part}}"><i class="fa fa-gims-population"></i> {{s.questionnaire}}<span ng-if="s.part != \'current\'">, {{s.part}}</span></span>' +
                '<span class="token" ng-switch-when="questionName" tooltip="Filter: {{s.filter}}, Questionnaire: {{s.questionnaire}}"><i class="fa fa-question"></i> {{s.filter}}<span ng-if="s.questionnaire != \'current\'">, {{s.questionnaire}}</span></span>' +
                '<span class="token" ng-switch-when="ruleValue" tooltip="Rule: {{s.rule}}, Questionnaire: {{s.questionnaire}}, Part: {{s.part}}"><i class="fa fa-gims-rule"></i> {{s.rule}}<span ng-if="s.questionnaire != \'current\'">, {{s.questionnaire}}</span><span ng-if="s.part != \'current\'">, {{s.part}}</span></span>' +
                '<span class="token" ng-switch-when="regressionFilterValue" tooltip="Filter: {{s.filter}}, Part: {{s.part}}, Year offset: {{s.year}}"><i class="fa fa-gims-filter" style="color: {{s.color}};"></i> {{s.filter}}<span ng-if="s.part != \'current\'">, {{s.part}}</span><span ng-if="s.year != \'0\'">, {{s.year}}</span></span>' +
                '<span class="token" ng-switch-when="regressionFilterValuesList" tooltip="Filter: {{s.filter}}"><i class="fa fa-gims-filterset"></i> {{s.filter}}</span>' +
                '<span class="token" ng-switch-when="regressionCumulatedPopulation" tooltip="Part: {{s.part}}"><i class="fa fa-gims-population"></i> {{s.part}}</span>' +
                '<span class="token" ng-switch-when="regressionYear">Current year</span>' +
                '</span>' +
                '</span>',
        scope: {
            structure: '='
        }
    };
});
