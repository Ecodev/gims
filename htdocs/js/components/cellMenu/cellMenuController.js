angular.module('myApp').controller('CellMenuCtrl', function($scope, $q, questionnaire, filter, part, questionnairesStatus, Restangular, TableFilter, Utility, $timeout) {
    'use strict';

    // My future self will hate me for this, but we hardcode the exclude
    // rule ID to make it easier to find it
    var excludeRuleId = 1;

    $scope.data = TableFilter.getData();
    $scope.questionnaire = questionnaire;
    $scope.filter = filter;
    $scope.part = part;
    $scope.questionnairesStatus = questionnairesStatus;

    // Expose function to scope
    $scope.deleteAnswer = TableFilter.deleteAnswer;
    $scope.isValidNumber = Utility.isValidNumber;
    $scope.getCellType = TableFilter.getCellType;

    /**
     * Returns whether the special Exclude rule exists in the given usage
     * @param {array} usages
     * @returns {boolean}
     */
    $scope.excludeRuleExists = function(usages) {
        return !!_.find(usages, function(usage) {
            return usage.rule.id == excludeRuleId;
        });
    };

    /**
     * Toggle the existence of the special Exclude rule, if exists removes it, if not add it
     * @param {questionnaire} questionnaire
     * @param {integer} filterId
     * @param {integer} partId
     */
    $scope.toggleExcludeRule = function(questionnaire, filterId, partId) {

        // Ensure that we have indeed some usages
        if (!questionnaire.filterQuestionnaireUsagesByFilterAndPart[filterId]) {
            questionnaire.filterQuestionnaireUsagesByFilterAndPart[filterId] = {};
        }
        if (!questionnaire.filterQuestionnaireUsagesByFilterAndPart[filterId][partId]) {
            questionnaire.filterQuestionnaireUsagesByFilterAndPart[filterId][partId] = {second: []};
        }

        var usages = questionnaire.filterQuestionnaireUsagesByFilterAndPart[filterId][partId].second;
        if ($scope.excludeRuleExists(usages)) {
            _.forEach(usages, function(usage) {
                if (usage.rule.id == excludeRuleId) {
                    Restangular.restangularizeElement(null, usage, 'filterQuestionnaireUsage');
                    usage.remove().then(function() {
                        questionnaire.filterQuestionnaireUsagesByFilterAndPart[filterId][partId].second = _.without(usages, usage);
                        TableFilter.refresh(false, true);
                    });
                }
            });
        } else {
            var usage = {
                isSecondStep: true,
                filter: filterId,
                questionnaire: questionnaire.id,
                part: partId,
                rule: excludeRuleId,
                justification: '', // should have something meaningful
                sorting: -1 // guarantee that the rule overrides all other existing rules
            };

            Restangular.all('filterQuestionnaireUsage').post(usage).then(function(newUsage) {
                usages.push(newUsage);
                TableFilter.refresh(false, true, true);
            });
        }
    };

    $scope.toggleQuestionAbsolute = function(questionnaire, question) {

        var isAbsolute = !question.isAbsolute;
        var questionnaireWithSameCode = TableFilter.getSurveysWithSameCode($scope.data.questionnaires, questionnaire.survey.code);

        _.forEach(questionnaireWithSameCode, function(questionnaire) {
            question = questionnaire.survey.questions[question.filter.id];

            var value = '';
            var max = '';

            if (isAbsolute) {
                question.isAbsolute = true;
                value = 'valueAbsolute';
                max = 10000000000000000;
            } else {
                question.isAbsolute = false;
                value = 'valuePercent';
                max = 100;
            }

            $timeout(function() {
                question.value = value;
                question.max = max;
                updateQuestion(questionnaire, question).then(function() {
                    TableFilter.refresh(false, true);
                });

            }, 0);
        });
    };

    /**
     * Update question considering questionnaire permissions
     * @param questionnaire
     * @param question
     */
    var updateQuestion = function(questionnaire, question) {
        var deferred = $q.defer();
        if (question.id && questionnairesStatus[questionnaire.status]) {
            Restangular.restangularizeElement(null, question, 'question');
            question.isLoading = true;
            question.put().then(function() {
                question.isLoading = false;
                deferred.resolve();
            }, function() {
                question.isLoading = false;
                deferred.reject();
            });
        }

        return deferred.promise;
    };

    $scope.sortableOptions = {
        stop: function(e, ui) {
            var questionnaireId = ui.item[0].dataset.questionnaire;
            var filterId = ui.item[0].dataset.filter;
            var partId = ui.item[0].dataset.part;
            var questionnaire = _.find($scope.data.questionnaires, {id: parseInt(questionnaireId)});
            var usages = questionnaire.filterQuestionnaireUsagesByFilterAndPart[filterId][partId].second.concat(questionnaire.filterQuestionnaireUsagesByFilterAndPart[filterId][partId].first);

            var miniUsages = [];
            // update sorting and create mini usages to avoid sending mass data to server
            _.forEach(usages, function(usage, i) {
                usage.sorting = i;
                miniUsages.push({
                    id: usage.id,
                    sorting: usage.sorting
                });
            });

            var usagesPromisses = [];
            _.forEach(miniUsages, function(usage) {
                Restangular.restangularizeElement(null, usage, 'filterQuestionnaireUsage');
                usagesPromisses.push(usage.put({fields: 'sorting'}));
            });

            $q.all(usagesPromisses).then(function() {
                TableFilter.refresh(false, true, true);
            });

        }
    };
});
