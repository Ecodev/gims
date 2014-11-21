/**
 * Controller for user editing within a modal
 */
angular.module('myApp').controller('selectExistingRuleModalCtrl', function($scope, $modalInstance, $timeout, referenceUsage, Restangular) {
    'use strict';

    $scope.referenceUsage = referenceUsage;

    $scope.ruleFields = {fields: 'structure'};

    $scope.types = ['filter', 'questionnaire', 'geoname'];
    $scope.suggestedUsages = {};

    $scope.$watch('referenceUsage', function() {
        if ($scope.referenceUsage) {
            updateFilterUsages();
            updateQuestionnaireUsages();
            updateGeonameUsages();
        }
    });

    var updateGeonameUsages = function() {
        $scope.suggestedUsages.geoname = [];
        if ($scope.referenceUsage.geoname && $scope.referenceUsage.geoname.id) {
            Restangular.one('geoname', $scope.referenceUsage.geoname.id).getList('filterGeonameUsage', {fields: 'rule.structure'}).then(function(usages) {
                $scope.suggestedUsages.geoname = _.uniq(usages, function(u) {
                    return u.rule.id;
                });
            });
        }
    };

    var updateFilterUsages = function() {
        if ($scope.referenceUsage.filter && $scope.referenceUsage.filter.id) {
            Restangular.one('filter', $scope.referenceUsage.filter.id).getList('filterQuestionnaireUsage', {fields: 'rule.structure'}).then(function(usages) {
                $scope.suggestedUsages.filter = _.uniq(usages, function(u) {
                    return u.rule.id;
                });
            });
        } else {
            $scope.suggestedUsages.filter = [];
        }
    };

    var updateQuestionnaireUsages = function() {
        if ($scope.referenceUsage.questionnaire && $scope.referenceUsage.questionnaire.id) {
            Restangular.one('questionnaire', $scope.referenceUsage.questionnaire.id).get({fields: 'filterQuestionnaireUsages.rule,questionnaireUsages.rule,filterQuestionnaireUsages.rule.structure,questionnaireUsages.rule.structure'}).then(function(object) {
                $scope.suggestedUsages.questionnaire = _.uniq(object.filterQuestionnaireUsages.concat(object.questionnaireUsages), function(u) {
                    return u.rule.id;
                });
            });
        } else {
            $scope.suggestedUsages.questionnaire = [];
        }
    };

    $scope.opened = true;
    $scope.selection = {};
    $scope.setRule = function(rule) {
        $scope.selection.rule = rule;
    };

    $scope.selectRule = function(rule) {
        $timeout(function() {
            $modalInstance.close(rule);
        });
    };

    $scope.$dismiss = function() {
        $modalInstance.dismiss();
    };
});
