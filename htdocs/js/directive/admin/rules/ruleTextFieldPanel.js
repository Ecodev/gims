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

            $rootScope.$on('gims-rule-usage-selected', function(evt, usage) {
                $scope.usage = usage;
                if (usage && usage.rule && usage.rule.id) {
                    Restangular.one('rule', usage.rule.id).get({fields: 'permissions,filterQuestionnaireUsages,questionnaireUsages,filterGeonameUsages'}).then(function(rule) {
                        $scope.usage.rule.nbOfUsages = rule.filterQuestionnaireUsages.length + rule.questionnaireUsages.length + rule.filterGeonameUsages.length;
                    });
                }
            });

            $scope.save = function() {
                if ($scope.usage.rule.id) {
                    $scope.isSaving = true;
                    AbstractModel.put($scope.usage.rule, 'rule').then(function() {
                        $scope.isSaving = false;
                        $scope.refresh({questionnairesPermissions: false, filtersComputing: true});
                    });
                } else {
                    $scope.isSaving = true;
                    AbstractModel.post($scope.usage.rule, 'rule').then(function() {
                        $scope.isSaving = false;
                        $scope.refresh({questionnairesPermissions: false, filtersComputing: true});
                    });
                }
            };

            $scope.delete = function() {
                $scope.isRemoving = true;
                AbstractModel.remove($scope.usage.rule, 'rule').then(function() {
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
