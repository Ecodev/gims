angular.module('myApp.directives').directive('gimsRuleTextFieldPanel', function(Restangular) {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with tag
        templateUrl: '/template/browse/rule/textFieldPanel',
        scope: {
            rule: '=',
            refresh: '&?',
            readonly: '='
        },
        controller: function($scope, AbstractModel) {
            $scope.$watch('rule', function(rule) {

                if (rule && rule.id) {
                    Restangular.one('rule', rule.id).get({fields: 'permissions,filterQuestionnaireUsages,questionnaireUsages,filterGeonameUsages'}).then(function(rule) {
                        $scope.rule.nbOfUsages = rule.filterQuestionnaireUsages.length + rule.questionnaireUsages.length + rule.filterGeonameUsages.length;
                    });
                }
            });

            $scope.save = function() {
                if ($scope.rule.id) {
                    $scope.isSaving = true;
                    AbstractModel.put($scope.rule, 'rule').then(function() {
                        $scope.isSaving = false;
                        $scope.refresh({questionnairesPermissions: false, filtersComputing: true});
                    });
                } else {
                    $scope.isSaving = true;
                    AbstractModel.post($scope.rule, 'rule').then(function() {
                        $scope.isSaving = false;
                        $scope.refresh({questionnairesPermissions: false, filtersComputing: true});
                    });
                }
            };

            $scope.delete = function() {
                $scope.isRemoving = true;
                AbstractModel.remove($scope.rule, 'rule').then(function() {
                    $scope.isRemoving = false;
                    $scope.close();
                });
            };

            $scope.close = function() {
                $scope.rule = null;
            };
        }
    };
});
