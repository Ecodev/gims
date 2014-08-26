angular.module('myApp.directives').directive('gimsRuleTextFieldPanel', function($rootScope, Restangular) {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with tag
        templateUrl: '/template/browse/rule/textFieldPanel',
        scope: {
            refresh: '&?',
            readonly: '='
        },
        controller: function($scope) {

            var ruleFields = {fields: 'permissions,filterQuestionnaireUsages,questionnaireUsages,filterGeonameUsages'};
            var usageFields = {fields: 'permissions,rule.permissions,rule.filterQuestionnaireUsages,rule.questionnaireUsages,rule.filterGeonameUsages'};

            $rootScope.$on('gims-rule-usage-added', function(evt, objects) {
                $scope.usage = {};
                $scope.usage.questionnaire = objects.questionnaire;
                $scope.usage.filter = objects.filter;
                $scope.usage.part = objects.part;
                $scope.usage.rule = {};
                $scope.showDetails = true;

                // for some unkown reason, the panel does not appear instantaneously,
                // especially when clicking on buttons in the very last row
                // of the table to add questionnaireUsage.
                $scope.$apply();
            });

            $rootScope.$on('gims-rule-usage-selected', function(evt, usage) {
                $scope.showDetails = false;
                $scope.usage = usage;
                getUsageProperties();
            });

            var getUsageProperties = function() {
                if ($scope.usage && $scope.usage.id) {
                    Restangular.one(getUsageType($scope.usage), $scope.usage.id).get(usageFields).then(function(usage) {
                        $scope.usage = usage;
                        $scope.usage.rule.nbOfUsages = usage.rule.filterQuestionnaireUsages.length + usage.rule.questionnaireUsages.length + usage.rule.filterGeonameUsages.length;
                    });
                }
            };

            var getUsageType = function(usage) {
                if (!usage) {
                    usage = $scope.usage;
                }
                if (usage.filter && usage.geoname) {
                    return 'filterGeonameUsage';
                } else if (usage.filter && usage.questionnaire) {
                    return 'filterQuestionnaireUsage';
                } else {
                    return 'questionnaireUsage';
                }
            };

            $scope.saveDuplicate = function() {
                delete $scope.usage.rule.id;
                $scope.usage.rule[getUsageType() + "s"] = [
                    {id: $scope.usage.id}
                ];
                $scope.usage.rule.nbOfUsages = 1;
                $scope.save();
            };

            /**
             * Create or update rule first, then create or update usage.
             */
            $scope.save = function() {

                // update
                if ($scope.usage.rule.id) {
                    $scope.isSaving = true;
                    $scope.usage.rule.put().then(function() {
                        $scope.isSaving = false;
                        $scope.refresh({questionnairesPermissions: false, filtersComputing: true, questionnairesUsages: true});
                    });

                    // create
                } else {
                    $scope.isSaving = true;
                    Restangular.all('Rule').post($scope.usage.rule, ruleFields).then(function(rule) {
                        $scope.usage.rule.id = rule.id;
                        $scope.usage.rule.permissions = rule.permissions;
                        $scope.saveUsage();
                        $scope.isSaving = false;
                    });
                }
            };

            /**
             * Create or update usage.
             */
            $scope.saveUsage = function() {

                // update
                if ($scope.usage.id) {
                    $scope.usage.put().then(function() {
                        $scope.refresh({questionnairesPermissions: false, filtersComputing: true, questionnairesUsages: true});
                    });

                    // create
                } else {
                    Restangular.all(getUsageType()).post($scope.usage).then(function(usage) {
                        $scope.usage.id = usage.id;
                        $scope.usage.rule[getUsageType()] = usage.id;
                        $scope.refresh({questionnairesPermissions: false, filtersComputing: true, questionnairesUsages: true});
                    });
                }
            };

            $scope.delete = function() {
                $scope.isRemoving = true;
                $scope.usage.remove().then(function() {
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
