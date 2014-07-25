angular.module('myApp.directives').directive('gimsRuleTextFieldPanel', function(Restangular) {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with tag
        template:   '<div class="formula-text-field-fixed" ng-show="rule">' +
                    '   <div class="row">' + '       <p class="col-sm-6">' +
                    '           <button class="btn btn-default" ng-click="close();" ><i class="fa fa-times"></i> Close</button>' +
                    '           <button class="btn btn-primary" ng-show="!readonly" ng-click="save()" ng-disabled="rule.permissions.update || errors.length"><i class="fa" ng-class="{\'fa-gims-loading\':isSaving, \'fa-check\':!isSaving}"></i> Save</button>' +
                    '           <button class="btn btn-danger" ng-show="!readonly" ng-disabled="rule.permissions.delete" ng-click="delete()"><i class="fa"  ng-class="{\'fa-gims-loading\':isRemoving, \'fa-trash-o\':!isRemoving}"></i> Delete</button>' +
                    '           <span class="text-warning" style="margin-left:10px;"><i class="fa fa-warning"></i> This rule is used in {{rule.nbOfUsages}} different places.</span>' +
                    '       </p>' +
                    '   </div>' +
                    '   <gims-rule-text-field rule="rule" messages="errors"></gims-rule-text-field>' +
                    '</div>',
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
