angular.module('myApp.directives').directive('gimsRuleTextFieldPanel', function($rootScope, Restangular, $q) {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with tag
        templateUrl: '/template/browse/rule/textFieldPanel',
        scope: {
            refresh: '&?',
            readonly: '='
        },
        controller: function($scope, selectExistingRuleModal) {

            var ruleFields = {fields: 'permissions,filterQuestionnaireUsages,questionnaireUsages,filterGeonameUsages'};
            var usageFields = {fields: 'permissions'};

            $rootScope.$on('gims-rule-usage-added', function(evt, objects) {
                $scope.usage = {};
                $scope.usage.questionnaire = objects.questionnaire;
                $scope.usage.geoname = objects.geoname;
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
            });

            $scope.$watch('usage', function(usage, oldUsage) {
                if (usage && usage.id && (_.isUndefined(oldUsage) || !_.isUndefined(oldUsage) && usage.id != oldUsage.id)) {
                    getUsageProperties(usage);
                }
            });

            $scope.$watch('usage.rule', function(rule, oldRule) {
                if (rule && rule.id && (_.isUndefined(oldRule) || !_.isUndefined(oldRule) && rule.id != oldRule.id)) {
                    getRuleProperties(rule);
                }
            });

            var getUsageProperties = _.debounce(function(usage) {
                Restangular.one(getUsageType(usage), $scope.usage.id).get(usageFields).then(function(newUsage) {
                    usage.permissions = newUsage.permissions;
                });
            }, 300);

            var getRuleProperties = _.debounce(function(rule) {
                Restangular.one('Rule', $scope.usage.rule.id).get(ruleFields).then(function(newRule) {
                    rule.permissions = newRule.permissions;
                    rule.filterQuestionnaireUsages = newRule.filterQuestionnaireUsages;
                    rule.questionnaireUsages = newRule.questionnaireUsages;
                    rule.filterGeonameUsages = newRule.filterGeonameUsages;
                    rule.nbOfUsages = rule.filterQuestionnaireUsages.length + rule.questionnaireUsages.length + rule.filterGeonameUsages.length;
                    $scope.setLastSelectedRule(rule);
                });
            }, 300);

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

            $scope.saveForAllParts = function() {
                $scope.usage.rule.formula = $scope.usage.rule.formula.replace(/P#\d/g, 'P#current');
            };

            $scope.selectExistingRule = function() {
                selectExistingRuleModal.select($scope.usage).then(function(rule) {
                    $scope.usage.rule = rule;
                    $scope.setLastSelectedRule(rule);
                });
            };

            $scope.setLastSelectedRule = function(rule) {
                $scope.lastSelectedRule = _.clone(rule);
                $scope.formulaForm.$setPristine(true);
            };

            $scope.restoreLastSelectedRule = function() {
                $scope.usage.rule = _.clone($scope.lastSelectedRule);
                $scope.formulaForm.$setPristine(true);
            };

            $scope.saveDuplicate = function() {
                delete $scope.usage.rule.id;
                $scope.usage.rule[getUsageType() + "s"] = [
                    {id: $scope.usage.id}
                ];
                $scope.usage.rule.nbOfUsages = 1;
                $scope.save();
            };

            $scope.save = function() {
                $scope.isSaving = true;

                saveRule().then(function(rule) {
                    if (rule) {
                        $scope.usage.rule.id = rule.id;
                        $scope.usage.rule.permissions = rule.permissions;
                    }

                    saveUsage().then(function(usage) {
                        if (usage) {
                            $scope.usage.id = usage.id;
                            $scope.usage.permissions = usage.permissions;
                        }
                        $scope.isSaving = false;
                        $scope.refresh({questionnairesPermissions: false, filtersComputing: true, questionnairesUsages: true});
                    });
                });
            };

            /**
             * Create or update rule first, then create or update usage.
             */
            var saveRule = function() {

                // update
                if ($scope.usage.rule.id && $scope.usage.rule.permissions && $scope.usage.rule.permissions.update) {
                    return $scope.usage.rule.put();

                    // create
                } else if ($scope.usage.rule && _.isUndefined($scope.usage.rule.id)) {
                    return Restangular.all('Rule').post($scope.usage.rule, ruleFields);

                    // do nothing, but return promise to allow script to process .then() function and save usage
                } else {
                    var deferred = $q.defer();
                    deferred.resolve();
                    return deferred.promise;
                }
            };

            /**
             * Create or update usage.
             */
            var saveUsage = function() {

                // update
                if ($scope.usage.id && $scope.usage.permissions && $scope.usage.permissions.update) {
                    return $scope.usage.put();

                    // create
                } else if ($scope.usage && _.isUndefined($scope.usage.id)) {
                    return Restangular.all(getUsageType()).post($scope.usage, usageFields);

                } else {
                    var deferred = $q.defer();
                    deferred.resolve();
                    return deferred.promise;
                }
            };

            $scope.delete = function() {
                $scope.isRemoving = true;
                Restangular.restangularizeElement(null, $scope.usage, getUsageType());
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
