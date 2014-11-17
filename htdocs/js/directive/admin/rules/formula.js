/**
 * Display a formula as an easy to read version (based on its structure)
 * <gims-formula structure="rule.structure" />
 */
angular.module('myApp.directives').directive('gimsFormula', function() {
    'use strict';
    return {
        restrict: 'E', // Only usage possible is with element
        template:
                '<div class="formula">' +
                '<span ng-repeat="s in structure">' +
                '<span ng-switch="s.type">' +
                '<span class="text"  ng-switch-when="text">{{s.content}}</span>' +
                '<span class="token" ng-switch-when="self">Ignore this rule</span>' +
                '<span class="token highlightColor" style="border-color: {{s.highlightColor}}" ng-switch-when="filterValue" tooltip="Filter: {{s.filter.name}}, Questionnaire: {{s.questionnaire.name}}, Part: {{s.part.name}}"><i class="fa fa-gims-filter"></i> {{s.filter.name}}<span ng-if="s.questionnaire.name != \'current\'">, {{s.questionnaire.name}}</span><span ng-if="s.part.name != \'current\'">, {{s.part.name}}</span></span>' +
                '<span class="token" ng-switch-when="populationValue" tooltip="Questionnaire: {{s.questionnaire.name}}, Part: {{s.part.name}}"><i class="fa fa-gims-population"></i> {{s.questionnaire.name}}<span ng-if="s.part.name != \'current\'">, {{s.part.name}}</span></span>' +
                '<span class="token" ng-switch-when="questionName" tooltip="Filter: {{s.filter.name}}, Questionnaire: {{s.questionnaire.name}}"><i class="fa fa-question"></i> {{s.filter.name}}<span ng-if="s.questionnaire.name != \'current\'">, {{s.questionnaire.name}}</span></span>' +
                '<span class="token highlightColor" style="border-color: {{s.highlightColor}}" ng-switch-when="ruleValue" tooltip="Rule: {{s.rule.name}}, Questionnaire: {{s.questionnaire.name}}, Part: {{s.part.name}}"><i class="fa fa-gims-rule"></i> {{s.rule.name}}<span ng-if="s.questionnaire.name != \'current\'">, {{s.questionnaire.name}}</span><span ng-if="s.part.name != \'current\'">, {{s.part.name}}</span></span>' +
                '<span class="token" ng-switch-when="regressionFilterValue" tooltip="Filter: {{s.filter.name}}, Part: {{s.part.name}}, Year offset: {{s.year}}"><i class="fa fa-gims-filter"></i> {{s.filter.name}}<span ng-if="s.part.name != \'current\'">, {{s.part.name}}</span><span ng-if="s.year != \'0\'">, {{s.year}}</span></span>' +
                '<span class="token" ng-switch-when="regressionFilterValuesList" tooltip="Filter: {{s.filter.name}}"><i class="fa fa-gims-filterset"></i> {{s.filter.name}}</span>' +
                '<span class="token" ng-switch-when="regressionCumulatedPopulation" tooltip="Part: {{s.part.name}}"><i class="fa fa-gims-population"></i> {{s.part.name}}</span>' +
                '<span class="token" ng-switch-when="regressionYear">Current year</span>' +
                '</span>' +
                '</span>' +
                '</div>',
        scope: {
            structure: '='
        }
    };
});
