angular.module('myApp.directives').directive('gimsRuleTextFieldPanel', function($rootScope, Restangular) {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with tag
        templateUrl: '/template/browse/rule/textFieldPanel',
        scope: {
            refresh: '&?',
            readonly: '='
        },
        controller: function($scope, AbstractModel) {

            var usageType = '';

            $rootScope.$on('gims-rule-usage-added', function(evt, objects) {
                $scope.usage = {};
                $scope.usage.questionnaire = objects.questionnaire;
                $scope.usage.filter = objects.filter;
                $scope.usage.part = objects.part;
                $scope.usage.rule = {};
                $scope.showDetails = true;
                setUsageType();
            });

            $rootScope.$on('gims-rule-usage-selected', function(evt, usage) {
                $scope.usage = usage;
                $scope.showDetails = false;
                if (usage && usage.rule && usage.rule.id) {
                    Restangular.one('rule', usage.rule.id).get({fields: 'permissions,filterQuestionnaireUsages,questionnaireUsages,filterGeonameUsages'}).then(function(rule) {
                        $scope.usage.rule.nbOfUsages = rule.filterQuestionnaireUsages.length + rule.questionnaireUsages.length + rule.filterGeonameUsages.length;
                        setUsageType();
                    });
                }
            });

            var setUsageType = function() {
                if ($scope.usage.filter && $scope.usage.geoname) {
                    usageType = 'filterGeonameUsage';
                } else if ($scope.usage.filter && $scope.usage.questionnaire) {
                    usageType = 'filterQuestionnaireUsage';
                } else if ($scope.usage.questionnaire && !$scope.usage.geoname && !$scope.usage.filter) {
                    usageType = 'questionnaireUsage';
                }
            };

            $scope.save = function() {
                if ($scope.usage.rule.id) {
                    $scope.isSaving = true;
                    AbstractModel.put($scope.usage.rule, 'rule').then(function() {
                        $scope.isSaving = false;
                        $scope.refresh({questionnairesPermissions: false, filtersComputing: true, questionnairesUsages: false}); // no need to request usages again, cause there is not new
                    });
                } else {
                    $scope.isSaving = true;
                    AbstractModel.post($scope.usage.rule, 'rule').then(function(rule) {
                        $scope.usage.rule.id = rule.id;
                        $scope.saveUsage();
                        $scope.isSaving = false;
                    });
                }
            };

            $scope.saveUsage = function() {

                if (!$scope.usage.id) {
                    var miniUsage = {
                        questionnaire: $scope.usage.questionnaire.id,
                        part: $scope.usage.part.id,
                        filter: $scope.usage.filter.id,
                        justification: $scope.usage.justification,
                        isSecondLevel: false,
                        rule: $scope.usage.rule.id
                    };

                    AbstractModel.post(miniUsage, usageType).then(function(usage) {
                        $scope.usage.id = usage.id;
                        $scope.usage.rule[usageType] = usage.id;
                        $scope.refresh({questionnairesPermissions: false, filtersComputing: true, questionnairesUsages: true}); // refresh usages to add added one
                    });
                } else {
                    $scope.refresh({questionnairesPermissions: false, filtersComputing: true, questionnairesUsages: false});
                }
            };

            $scope.delete = function() {
                $scope.isRemoving = true;
                AbstractModel.remove($scope.usage.rule, 'rule').then(function() {
                    $scope.refresh({questionnairesPermissions: false, filtersComputing: true, questionnairesUsages: true});
                    $scope.isRemoving = false;
                    $scope.close();
                });
            };

            $scope.close = function() {
                $scope.usage = null;
            };
        }
    };
});
