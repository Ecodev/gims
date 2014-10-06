angular.module('myApp.directives').directive('gimsRuleTextFieldPanel', function($rootScope, Restangular, $q, requestNotification) {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with tag
        templateUrl: '/template/browse/rule/textFieldPanel',
        scope: {
            refresh: '&?',
            readonly: '='
        },
        controller: function($scope, selectExistingRuleModal) {

            var ruleFields = {fields: 'permissions,filterQuestionnaireUsages.isSecondStep,questionnaireUsages,filterGeonameUsages'};
            var usageFields = {fields: 'permissions,isSecondStep'};
            $scope.usageType = null;

            $scope.isLoading = false;
            requestNotification.subscribeOnRequest(function() {
                $scope.isLoading = true;
            }, function() {
                if (requestNotification.getRequestCount() === 0) {
                    $scope.isLoading = false;
                }
            });

            $rootScope.$on('gims-rule-usage-added', function(evt, objects) {
                $scope.usage = {};
                $scope.usage.questionnaire = objects.questionnaire;
                $scope.usage.geoname = objects.geoname;
                $scope.usage.filter = objects.filter;
                $scope.usage.part = objects.part;
                $scope.usage.rule = {};
                $scope.showDetails = true;
                updateUsageType();

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
                if (usage && usage.id && (!oldUsage || oldUsage && usage.id != oldUsage.id)) {
                    updateUsageType();
                    getUsageProperties(usage);
                }
            });

            $scope.$watch('usage.rule', function(rule, oldRule) {
                if (rule && rule.id && (!oldRule || oldRule && rule.id != oldRule.id)) {
                    getRuleProperties(rule);
                }
            });

            var getUsageProperties = _.debounce(function(usage) {
                Restangular.one($scope.usageType, $scope.usage.id).get(usageFields).then(function(newUsage) {
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
                    $scope.saveCheckPoint();
                });
            }, 300);

            var updateUsageType = function(usage) {
                if (!usage) {
                    usage = $scope.usage;
                }

                if (usage && usage.filter && usage.geoname) {
                    $scope.usageType = 'filterGeonameUsage';
                } else if (usage && usage.filter && usage.questionnaire) {
                    $scope.usageType = 'filterQuestionnaireUsage';
                } else if (usage && usage.questionnaire && !usage.filter) {
                    $scope.usageType = 'questionnaireUsage';
                } else {
                    $scope.usageType = null;
                }
            };

            $scope.isFQU = function() {
                return $scope.usageType == 'filterQuestionnaireUsage';
            };

            $scope.saveForAllParts = function() {
                $scope.usage.rule.formula = $scope.usage.rule.formula.replace(/P#\d/g, 'P#current');
            };

            $scope.selectExistingRule = function() {
                selectExistingRuleModal.select($scope.usage).then(function(rule) {
                    $scope.usage.rule = rule;
                    $scope.saveCheckPoint();
                });
            };

            $scope.saveCheckPoint = function() {
                $scope.originUsage = _.cloneDeep($scope.usage);
                if ($scope.form) {
                    $scope.form.$setPristine();
                }
            };

            $scope.resetForm = function() {
                $scope.usage = _.cloneDeep($scope.originUsage);
                if ($scope.form) {
                    $scope.form.$setPristine();
                }
            };

            $scope.saveDuplicate = function() {
                delete $scope.usage.rule.id;
                $scope.usage.rule[$scope.usageType + "s"] = [
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
                        if (rule.permissions) {
                            $scope.usage.rule.permissions = rule.permissions;
                        }
                    }

                    saveUsage().then(function(usage) {
                        if (usage) {
                            $scope.usage.id = usage.id;
                            if (usage.permissions) {
                                $scope.usage.permissions = usage.permissions;
                            }
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

                Restangular.restangularizeElement(null, $scope.usage.rule, 'Rule');

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

                Restangular.restangularizeElement(null, $scope.usage, $scope.usageType);

                // update
                if ($scope.usage.id && $scope.usage.permissions && $scope.usage.permissions.update) {
                    return $scope.usage.put();

                    // create
                } else if ($scope.usage && _.isUndefined($scope.usage.id)) {
                    return Restangular.all($scope.usageType).post($scope.usage, usageFields);

                } else {
                    var deferred = $q.defer();
                    deferred.resolve();
                    return deferred.promise;
                }
            };

            $scope.delete = function() {
                $scope.isRemoving = true;

                Restangular.restangularizeElement(null, $scope.usage, $scope.usageType);
                Restangular.restangularizeElement(null, $scope.usage.rule, 'Rule');

                $scope.usage.remove().then(function() {
                    if ($scope.usage.rule.nbOfUsages == 1) { // if current usage is the only one used by rule, delete rule too
                        $scope.usage.rule.remove().then(function() {
                            $scope.isRemoving = false;
                            $scope.refresh({questionnairesPermissions: false, filtersComputing: true, questionnairesUsages: true});
                            $scope.close();
                        });
                    } else {
                        $scope.isRemoving = false;
                        $scope.refresh({questionnairesPermissions: false, filtersComputing: true, questionnairesUsages: true});
                        $scope.close();
                    }
                });
            };

            $scope.close = function() {
                $scope.usage = null;
            };
        }
    };
});
