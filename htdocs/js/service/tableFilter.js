/**
 * Service with common functions to manage /browse/table/filter and its contextual menu
 */
angular.module('myApp.services').factory('TableFilter', function($http, $q, Restangular, questionnairesStatus, Percent, Utility) {
    'use strict';

    /**
     * This contains all data needed to display table filter.
     * It is passed around different controller/directives, but
     * must always stays the same object
     * @type object
     */
    var data;

    /**
     * Initialize and return the data container for this service
     * @param {string} locationPath
     * @returns {object}
     */
    function init(locationPath) {
        data = {};

        var modes = [
            {
                name: 'Browse',
                isContribute: false,
                isSector: false,
                surveyType: 'jmp,nsa'
            },
            {
                name: 'Contribute JMP',
                isContribute: true,
                isSector: false,
                surveyType: 'jmp'
            },
            {
                name: 'Contribute NSA',
                isContribute: true,
                isSector: true,
                surveyType: 'nsa'
            }
        ];

        if (locationPath.indexOf('/nsa') >= 0) {
            data.mode = modes[2];
        } else if (locationPath.indexOf('/contribute') >= 0) {
            data.mode = modes[1];
        } else if (locationPath.indexOf('/browse') >= 0) {
            data.mode = modes[0];
        }

        return getData();
    }

    /**
     * Returns the data containers
     * @returns {object}
     */
    function getData() {
        return data;
    }

    /**
     * Call api to get answer permissions
     * @param question
     * @param answer
     * @param questionnaire
     */
    function getPermissions(question, answer, questionnaire) {

        var deferred = $q.defer();

        if (answer.id && _.isUndefined(answer.permissions)) {
            Restangular.one('answer', answer.id).get({fields: 'permissions'}).then(function(newAnswer) {
                answer.permissions = newAnswer.permissions;

                // if value has been updated between permissions check, restore value
                if (!answer.permissions.update) {
                    answer.displayValue = question.isAbsolute ? newAnswer[question.value] : Percent.fractionToPercent(newAnswer[question.value]);
                }

                answer.isLoading = false;
                deferred.resolve(answer);

            }, function() {
                answer.isLoading = false;
                answer.permissions = {
                    create: false,
                    read: false,
                    update: false,
                    delete: false
                };

                deferred.resolve(answer);
            });
        } else if (questionnaire) {
            answer.permissions = {
                create: questionnairesStatus[questionnaire.status],
                read: questionnairesStatus[questionnaire.status],
                update: questionnairesStatus[questionnaire.status],
                delete: questionnairesStatus[questionnaire.status]
            };
        }

        return deferred.promise;
    }

    /**
     * Delete answer considering answer permissions
     * @param answer
     */
    function deleteAnswer(answer) {

        if (answer.id && answer.permissions.delete) {
            answer.isLoading = true;
            answer.remove().then(function() {
                delete(answer.id);
                delete(answer.displayValue);
                delete(answer.edit);
                answer.isLoading = false;
                refresh(false, true);
            });
        }
    }

    /**
     * Remove question after retrieving permissions from server if not yet done
     * @param question
     * @param answer
     */
    function removeAnswer(question, answer) {
        Restangular.restangularizeElement(null, answer, 'answer');
        if (_.isUndefined(answer.permissions)) {
            getPermissions(question, answer).then(function() {
                deleteAnswer(answer);
            });
        } else {
            deleteAnswer(answer);
        }
    }

    function getSurveysWithSameCode(questionnaires, code) {
        if (!_.isUndefined(code)) {
            var c2 = _.isNumber(code) ? code : code.toUpperCase();
            var questionnairesWithSameCode = _.filter(questionnaires, function(q) {
                if (!_.isUndefined(q.survey.code)) {
                    var c1 = _.isNumber(q.survey.code) ? q.survey.code : q.survey.code.toUpperCase();
                    if (c1 == c2) {
                        return true;
                    }
                }
            });

            return questionnairesWithSameCode;
        } else {
            return [];
        }
    }

    /**
     * Call questionnaires asking for passed fields and executing callback function passing received questionnaires
     * @param questionnaires
     * @param fields
     */
    function getQuestionnaires(questionnaires, fields) {
        var deferred = new $q.defer();

        if (questionnaires.length === 1 && !_.isUndefined(questionnaires[0])) {
            Restangular.one('questionnaire', questionnaires[0]).get(fields).then(function(questionnaire) {
                deferred.resolve([questionnaire]);
            });
        } else if (questionnaires.length > 1) {
            Restangular.all('questionnaire').getList(_.merge({id: questionnaires.join(',')}, fields)).then(function(questionnaires) {

                // when retrieve questionnaire with read permissions, remove pré-selected questionnaires from list if they're not received
                var removedQuestionnaires = _.difference(_.pluck(data.questionnaires, 'id'), _.pluck(questionnaires, 'id'));
                _.forEach(removedQuestionnaires, function(questionnaireId) {
                    var index = _.findIndex(data.questionnaires, {id: questionnaireId});
                    if (index >= 0) {
                        data.questionnaires.splice(index, 1);
                    }
                });

                deferred.resolve(questionnaires);
            }, function() {
                data.questionnaires = [];
            });
        }

        return deferred.promise;
    }

    /**
     * Update questionnaires permissions from new one to old one
     * @param {type} oldQuestionnaires
     * @param {type} newQuestionnaires
     */
    function updateQuestionnairePermissions(oldQuestionnaires, newQuestionnaires) {
        _.forEach(oldQuestionnaires, function(questionnaire) {
            questionnaire.permissions = _.find(newQuestionnaires, {id: questionnaire.id}).permissions;
        });
    }

    /**
     * Update questionnaires Usages from new one to old one
     * @param {type} oldQuestionnaires
     * @param {type} newQuestionnaires
     */
    function updateQuestionnaireUsages(oldQuestionnaires, newQuestionnaires) {
        _.forEach(oldQuestionnaires, function(questionnaire) {
            questionnaire.filterQuestionnaireUsages = _.find(newQuestionnaires, {id: questionnaire.id}).filterQuestionnaireUsages;
            indexFilterQuestionnaireUsages(questionnaire);
        });
    }

    /**
     * Refreshing page means :
     *  - Recover all questionnaires permissions (in case user switch from browse to contribute/full view and need to be logged in)
     *  - Recompute filters, after some changes on answers. Can be done automatically after each answer change, but is heavy.
     * @param {type} questionnairesPermissions
     * @param {type} filtersComputing
     * @param {type} questionnairesUsages
     */
    function refresh(questionnairesPermissions, filtersComputing, questionnairesUsages) {

        if (questionnairesPermissions && !_.isUndefined(data) && !_.isUndefined(data.questionnaires)) {
            getQuestionnaires(_.pluck(data.questionnaires, 'id'), {fields: 'permissions'}).then(function(questionnaires) {
                updateQuestionnairePermissions(data.questionnaires, questionnaires);
            });
        }

        if (questionnairesUsages && !_.isUndefined(data) && !_.isUndefined(data.questionnaires)) {
            getQuestionnaires(_.pluck(data.questionnaires, 'id'), {fields: 'filterQuestionnaireUsages.isSecondStep,filterQuestionnaireUsages.sorting'}).then(function(questionnaires) {
                updateQuestionnaireUsages(data.questionnaires, questionnaires);
            });
        }

        if (filtersComputing) {
            getComputedFilters();
        }
    }

    /**
     * Init computed filters
     * @type {null}
     */
    var getComputedFiltersCanceller = null;
    function getComputedFilters() {
        if (_.isEmpty(data.questionnaires)) {
            return;
        }

        var filtersIds = _.pluck(data.filters, 'id');
        var questionnairesIds = _.compact(_.pluck(data.questionnaires, 'id')); // compact remove falsey values

        if (filtersIds.length > 0 && questionnairesIds.length > 0) {
            data.isComputing = true;

            if (getComputedFiltersCanceller) {
                getComputedFiltersCanceller.resolve();
            }
            getComputedFiltersCanceller = $q.defer();

            $http.get('/api/filter/getComputedFilters', {
                timeout: getComputedFiltersCanceller.promise,
                params: {
                    filters: filtersIds.join(','),
                    questionnaires: questionnairesIds.join(',')
                }
            }).success(function(questionnaires) {

                // Complete our structure with the result from server
                _.forEach(data.questionnaires, function(scopeQuestionnaire) {
                    if (questionnaires[scopeQuestionnaire.id]) {
                        _.forEach(questionnaires[scopeQuestionnaire.id], function(valuesByPart, filterId) {

                            // Compute a few useful things for our template
                            _.forEach(valuesByPart, function(values, partId) {
                                valuesByPart[partId].isExcludedFromComputing = !Utility.isValidNumber(values.second) && Utility.isValidNumber(values.first);
                                valuesByPart[partId].displayValue = Percent.fractionToPercent(Utility.isValidNumber(values.second) ? values.second : values.first);
                            });

                            scopeQuestionnaire.survey.questions[filterId].filter.values = valuesByPart;
                        });
                    }
                });
                data.isComputing = false;
            });

            // Also get questionnaireUsages for all questionnaires, if showing them
            if (data.showQuestionnaireUsages) {
                loadQuestionnaireUsages();
            }
        }
    }

    /**
     * Load questionnaireUsages and their computed values for all questionnaires
     * @returns {undefined}
     */
    function loadQuestionnaireUsages() {
        var questionnairesIds = _.compact(_.pluck(data.questionnaires, 'id')).join(','); // compact remove falsey values

        $http.get('/api/questionnaireUsage/compute', {
            timeout: getComputedFiltersCanceller.promise,
            params: {
                questionnaires: questionnairesIds
            }
        }).success(function(questionnaireUsages) {
            data.questionnaireUsages = questionnaireUsages;
        });
    }

    /**
     * Toggle the display of questionnaireUsages
     */
    function toggleShowQuestionnaireUsages() {
        data.showQuestionnaireUsages = !data.showQuestionnaireUsages;

        if (data.showQuestionnaireUsages && !data.questionnaireUsages) {
            loadQuestionnaireUsages();
        }
    }

    /**
     * Returns the type of cell, to be able to display correct icon
     * @param {questionnaire} questionnaire
     * @param {filter} filter
     * @param {integer} partId
     * @returns {String}
     */
    function getCellType(questionnaire, filter, partId) {

        if (questionnaire.survey) {

            var question = questionnaire.survey.questions[filter.id];
            var answer;
            if (question && question.answers) {
                answer = question.answers[partId];
            }

            if (question && question.isLoading || (answer && answer.isLoading)) {
                return 'loading';
            }

            var firstValue;
            if (question && question.filter.values && question.filter.values[partId]) {
                firstValue = question.filter.values[partId].first;
            }

            var usages;
            if (questionnaire.filterQuestionnaireUsagesByFilterAndPart && questionnaire.filterQuestionnaireUsagesByFilterAndPart[filter.id]) {
                usages = questionnaire.filterQuestionnaireUsagesByFilterAndPart[filter.id][partId].first.concat(questionnaire.filterQuestionnaireUsagesByFilterAndPart[filter.id][partId].second);
            }

            if (answer && answer.error) {
                return 'error';
            } else if (answer && Utility.isValidNumber(answer[question.value])) {
                return 'answer';
            } else if (usages && usages.length) {
                return 'rule';
            } else if (filter.summands && filter.summands.length && Utility.isValidNumber(firstValue)) {
                return 'summand';
            } else if (Utility.isValidNumber(firstValue)) {
                return 'child';
            }
        }

        return 'nothing';
    }

    /**
     * Index FilterQuestionnaireUsages by filter and part
     * @param questionnaire
     */
    function indexFilterQuestionnaireUsages(questionnaire) {
        // Indexes usages by filter and part
        var usagesByFilter = {};
        _.forEach(questionnaire.filterQuestionnaireUsages, function(usage) {

            if (!usagesByFilter[usage.filter.id]) {
                usagesByFilter[usage.filter.id] = {};
            }

            if (!usagesByFilter[usage.filter.id][usage.part.id]) {
                usagesByFilter[usage.filter.id][usage.part.id] = {first: [], second: []};
            }
            if (usage.isSecondStep) {
                usagesByFilter[usage.filter.id][usage.part.id].second.push(usage);
            } else {
                usagesByFilter[usage.filter.id][usage.part.id].first.push(usage);
            }
        });

        questionnaire.filterQuestionnaireUsagesByFilterAndPart = usagesByFilter;

    }

    // Return public API
    return {
        init: init,
        getData: getData,
        removeAnswer: removeAnswer,
        getPermissions: getPermissions,
        getSurveysWithSameCode: getSurveysWithSameCode,
        getQuestionnaires: getQuestionnaires,
        refresh: refresh,
        getComputedFilters: getComputedFilters,
        toggleShowQuestionnaireUsages: toggleShowQuestionnaireUsages,
        getCellType: getCellType,
        indexFilterQuestionnaireUsages: indexFilterQuestionnaireUsages
    };
});
