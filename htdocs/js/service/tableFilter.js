/**
 * Service with common functions to manage /browse/table/filter and its contextual menu
 */
angular.module('myApp.services').factory('TableFilter', function($rootScope, $http, $q, $location, $timeout, Restangular, questionnairesStatus, Percent, Utility) {
    'use strict';

    /**
     * This contains all data needed to display table filter.
     * It is passed around different controller/directives, but
     * must always stays the same object
     * @type object
     */
    var data;

    var questionnaireWithAnswersFields = {fields: 'status,permissions,comments,geoname,survey.questions,survey.questions.isAbsolute,survey.questions.filter,survey.questions.alternateNames,survey.questions.answers.questionnaire,survey.questions.answers.part,populations.part,filterQuestionnaireUsages.isSecondStep,filterQuestionnaireUsages.sorting'};
    var questionnaireFields = {fields: 'survey.questions.type,survey.questions.filter'};

    var lastUnsavedFilterId = 1;

    /**
     * Initialize and return the data container for this service
     * @param {string} locationPath
     * @returns {object}
     */
    function init(locationPath) {
        data = {
            parts: Restangular.all('part').getList().$object
        };

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
        deferred.promise.then(function() {
            $rootScope.$emit('gims-tablefilter-permissions-changed');
        });

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

            deferred.resolve(answer);
        } else if (answer.permissions) {
            deferred.resolve(answer);
        }

        return deferred.promise;
    }

    /**
     * Delete answer considering answer permissions
     * @param question
     * @param answer
     */
    function deleteAnswer(question, answer) {
        getPermissions(question, answer).then(function() {
            if (answer.id && answer.permissions.delete) {
                answer.isLoading = true;
                Restangular.restangularizeElement(null, answer, 'answer');
                answer.remove().then(function() {

                    delete(answer.id);
                    delete(answer.valuePercent);
                    delete(answer.valueAbsolute);
                    delete(answer.initialValue);
                    delete(answer.displayValue);
                    delete(answer.edit);
                    answer.isLoading = false;
                    refresh(false, true);
                });
            }
        });
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
     * If a question has no value for isAbsolute attribute, set is by percent by default
     * @todo adapt to sector, default to absolute and replace everywhere where those values are set
     * @param question
     */
    function initQuestionAbsolute(question) {

        // if absolute is undefined and we are in contribute view (no sector) -> set question as percent by default
        if (_.isUndefined(question.isAbsolute) && !data.mode.isSector || question && question.isAbsolute === false) {
            question.isAbsolute = false;
            question.value = 'valuePercent';
            question.max = 100;

            // if absolute is undefined and we are in sector view -> set question as absolute by default
        } else if (_.isUndefined(question.isAbsolute) && data.mode.isSector || question && question.isAbsolute === true) {
            question.isAbsolute = true;
            question.value = 'valueAbsolute';
            question.max = 100000000000;
        }

        return question;
    }

    /**
     * Get an empty answer ready to be used as model and with right attributs set
     * @param {answer} answer
     * @param {questionnaire} questionnaire
     * @param {question} question
     * @param {part} part
     * @returns {answer}
     */
    function getEmptyAnswer(answer, questionnaire, question, part) {
        answer = answer || {};

        if (questionnaire) {
            answer.questionnaire = questionnaire;
        }

        if (question) {
            answer.question = question;
        }

        if (part) {
            answer.part = part;
        }

        return answer;
    }

    /**
     * Define the width needed to display the questionnaire column
     * @param {type} questionnaire
     */
    function setQuestionnaireWidth(questionnaire) {
        if (questionnaire.showLabels) {
            questionnaire.width = 775;
        } else {
            questionnaire.width = 469;
        }
    }

    /**
     * Called when a new questionnaire is added or filters are changed.
     * Ensure there is empty objects to grant app to work fine (e.g emptyanswers have to exist before ng-model assigns a value)
     */
    function fillMissingElements() {
        if (data.questionnaires && data.filters) {

            _.forEach(data.questionnaires, function(questionnaire) {

                setQuestionnaireWidth(questionnaire);
                if (_.isUndefined(questionnaire.survey)) {
                    questionnaire.survey = {
                        type: data.mode.surveyType
                    };
                }

                if (_.isUndefined(questionnaire.status)) {
                    questionnaire.status = 'new';
                }

                if (_.isUndefined(questionnaire.survey.questions)) {
                    questionnaire.survey.questions = {};
                }

                // Create empty population indexed by the part ID
                if (_.isUndefined(questionnaire.populations)) {
                    questionnaire.populations = {};
                }

                _.forEach(data.parts, function(part) {
                    if (_.isUndefined(questionnaire.populations[part.id])) {
                        questionnaire.populations[part.id] = {
                            part: part
                        };
                    }
                });

                // Complete questions
                _.forEach(data.filters, function(filter) {
                    if (_.isUndefined(questionnaire.survey.questions[filter.id])) {
                        questionnaire.survey.questions[filter.id] = {
                            filter: {id: filter.id},
                            parts: [1, 2, 3],
                            type: 'Numeric',
                            alternateNames: {}
                        };
                    }
                    if (filter.sectorChild) {
                        questionnaire.survey.questions[filter.id].isAbsolute = true;
                    }

                    initQuestionAbsolute(questionnaire.survey.questions[filter.id]);

                    if (_.isUndefined(questionnaire.survey.questions[filter.id].answers)) {
                        questionnaire.survey.questions[filter.id].answers = {};
                    }

                    _.forEach(data.parts, function(part) {
                        questionnaire.survey.questions[filter.id].answers[part.id] = getEmptyAnswer(questionnaire.survey.questions[filter.id].answers[part.id], questionnaire.id, questionnaire.survey.questions[filter.id].id, part.id);
                    });
                });

            });
        }
    }

    /**
     * Index answers and populations by part and questions by filters on questionnaire that have data from DB
     * @param questionnaires
     */
    function prepareDataQuestionnaires(questionnaires) {
        angular.forEach(questionnaires, function(questionnaire) {

            _.forEach(questionnaire.survey.questions, function(question) {
                if (question.answers) {

                    // index answers by part id
                    var answers = {};
                    _.forEach(question.answers, function(answer) {
                        if (answer.questionnaire && answer.questionnaire.id == questionnaire.id) {
                            answer.initialValue = question.isAbsolute ? answer.valueAbsolute : answer.valuePercent;
                            answer.displayValue = question.isAbsolute ? answer.valueAbsolute : Percent.fractionToPercent(answer.valuePercent);
                            answers[answer.part.id] = answer;
                        }
                    });
                    question.answers = answers;
                }
            });

            // Index population by part id
            questionnaire.populations = _.indexBy(questionnaire.populations, function(p) {
                return p.part.id;
            });

            _.forEach(questionnaire.survey.questions, function(question) {
                initQuestionAbsolute(question);
            });

            questionnaire.survey.questions = _.indexBy(questionnaire.survey.questions, function(q) {
                return q.filter.id;
            });

            indexFilterQuestionnaireUsages(questionnaire);

            // update data with modified questionnaire
            data.questionnaires[_.findIndex(data.questionnaires, {id: questionnaire.id})] = questionnaire;
        });

        fillMissingElements();
        getComputedFilters();
    }

    /**
     * Load questionnaires from DB and prepare them for display
     * @param newQuestionnaires
     */
    function loadQuestionnaires(newQuestionnaires) {
        var deferred = new $q.defer();

        getQuestionnaires(newQuestionnaires, questionnaireWithAnswersFields).then(function(questionnaires) {
            prepareDataQuestionnaires(questionnaires);
            deferred.resolve(questionnaires);
        });

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
    function updateFilterQuestionnaireUsages(oldQuestionnaires, newQuestionnaires) {
        _.forEach(oldQuestionnaires, function(questionnaire) {
            questionnaire.filterQuestionnaireUsages = _.find(newQuestionnaires, {id: questionnaire.id}).filterQuestionnaireUsages;
            indexFilterQuestionnaireUsages(questionnaire);
        });
    }

    /**
     * Refreshing page means :
     *  - Recover all questionnaires permissions (in case user switch from browse to contribute/full view and need to be logged in)
     *  - Recompute filters, after some changes on answers. Can be done automatically after each answer change, but is heavy.
     * @param {bool} questionnairesPermissions
     * @param {bool} filtersComputing
     * @param {bool} questionnairesUsages
     */
    function refresh(questionnairesPermissions, filtersComputing, questionnairesUsages) {

        if (questionnairesPermissions && !_.isUndefined(data) && !_.isUndefined(data.questionnaires)) {
            getQuestionnaires(_.pluck(data.questionnaires, 'id'), {fields: 'permissions'}).then(function(questionnaires) {
                updateQuestionnairePermissions(data.questionnaires, questionnaires);
            });
        }

        if (filtersComputing) {
            getComputedFilters();
        }

        if (questionnairesUsages && !_.isUndefined(data) && !_.isUndefined(data.questionnaires)) {
            getQuestionnaires(_.pluck(data.questionnaires, 'id'), {fields: 'filterQuestionnaireUsages.isSecondStep,filterQuestionnaireUsages.sorting'}).then(function(questionnaires) {
                updateFilterQuestionnaireUsages(data.questionnaires, questionnaires);
            });
        }
    }

    /**
     * Init computed filters
     */
    var previousQuery = null;
    function getComputedFilters() {
        if (_.isEmpty(data.questionnaires)) {
            return;
        }

        var filtersIds = _.pluck(data.filters, 'id');
        var questionnairesIds = _.compact(_.pluck(data.questionnaires, 'id')); // compact remove falsey values

        if (filtersIds.length > 0 && questionnairesIds.length > 0) {

            var params = {
                filters: filtersIds.join(','),
                questionnaires: questionnairesIds.join(',')
            };

            // If there is a previous query still running...
            if (previousQuery) {
                if (_.isEqual(previousQuery.params, params)) {
                    // ... and it is the same as what we are going to do, don't do anything
                    return;
                } else {
                    // ... and it is different, cancel the previous one, and run ours
                    previousQuery.canceller.resolve();
                }
            }

            data.isComputing = true;
            previousQuery = {
                canceller: $q.defer(),
                params: params
            };

            $http.get('/api/filter/getComputedFilters', {
                timeout: previousQuery.promise,
                params: params
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
                previousQuery = null;

                // Notify that at this point, we have everything to fully display tableFilter
                $rootScope.$emit('gims-tablefilter-computed');
            });

            // Also get questionnaireUsages for all questionnaires, if showing them
            if (data.showQuestionnaireUsages) {
                loadQuestionnaireUsages();
            }

            refresh(false, false, true);
        }
    }

    /**
     * Load questionnaireUsages and their computed values for all questionnaires
     * @returns {undefined}
     */
    function loadQuestionnaireUsages() {
        var questionnairesIds = _.compact(_.pluck(data.questionnaires, 'id')).join(','); // compact remove falsey values

        $http.get('/api/questionnaireUsage/compute', {
            timeout: previousQuery ? previousQuery.promise : null,
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

            var usages = {};
            if (questionnaire.filterQuestionnaireUsagesByFilterAndPart && questionnaire.filterQuestionnaireUsagesByFilterAndPart[filter.id]) {
                usages = questionnaire.filterQuestionnaireUsagesByFilterAndPart[filter.id][partId] || {};
            }

            if (answer && answer.error) {
                return 'error';
            } else if (answer && Utility.isValidNumber(answer[question.value])) {
                return 'answer';
            } else if (usages && ((usages.first && usages.first.length) || (usages.second && usages.second.length))) {
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
        $rootScope.$emit('gims-tablefilter-computed');
    }

    /**
     * Update parameters on url excluding empty IDs to avoid multiple consecutive commas that cause problems on server side.
     * @param element
     */
    function updateUrl(element) {
        $location.search(element, _.filter(_.pluck(data[element], 'id'), function(el) {
            if (el) {
                return true;
            }
        }).join(','));
    }

    /**
     * Detects if a ID is temporary (with underscore) or not. Used to detect unsaved filters.
     * Return true if filter has a numeric ID without underscore or false if not.
     * @param filter
     * @returns {boolean}
     */
    function isValidId(filter) {
        if (_.isUndefined(filter.id) || /_\d+/.test(filter.id)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * When saving a filter with temporary url, we need to update question filter and index with new filter id.
     * @param filter
     */
    function replaceQuestionsIds(filter) {
        _.forEach(data.questionnaires, function(questionnaire) {
            if (questionnaire.survey && questionnaire.survey.questions && questionnaire.survey.questions[filter.oldId]) {
                questionnaire.survey.questions[filter.id] = questionnaire.survey.questions[filter.oldId];
                questionnaire.survey.questions[filter.id].filter.id = filter.id;
                delete(questionnaire.survey.questions[filter.oldId]);
            }
        });
    }

    /**
     * Remplace id related to filters that have temporary id by the new ID returned by DB.
     * @param filter
     */
    function replaceIdReferenceOnChildFilters(filter) {
        _.forEach(data.filters, function(f) {
            _.forEach(f.parents, function(parent) {
                if (parent.id == filter.oldId) {
                    parent.id = filter.id;
                }
            });
        });
    }

    /**
     * Save a single filter
     * @param filter
     * @returns promise
     */
    function saveFilter(filter) {
        var deferred = $q.defer();

        if (filter.id) {
            filter.oldId = filter.id;
        }
        filter.isLoading = true;
        Restangular.all('filter').post({name: filter.name, parents: _.pluck(filter.parents, 'id'), filterSets: filter.filterSets}).then(function(newFilter) {
            filter.id = newFilter.id;
            filter.isLoading = false;
            replaceQuestionsIds(filter);
            replaceIdReferenceOnChildFilters(filter);
            deferred.resolve(filter);
        });

        return deferred.promise;
    }

    /**
     * Save given collection of filters
     * @param filtersToSave
     * @returns promise
     */
    function saveFiltersCollection(filtersToSave) {
        var deferred = $q.defer();

        if (filtersToSave.length === 0) {
            deferred.resolve();
        } else {
            var filterPromises = [];
            _.forEach(filtersToSave, function(filter) {
                filterPromises.push(saveFilter(filter));
            });
            $q.all(filterPromises).then(function() {
                updateUrl('filters');
                deferred.resolve();
            });
        }

        return deferred.promise;
    }

    function saveEquipments() {
        var deferred = $q.defer();

        // get all filters with ID starting by _1
        var equipments = _.filter(data.filters, function(f) {
            if (/^_\d+/.test(f.id)) {
                return true;
            }
        });

        if (_.isEmpty(equipments)) {
            deferred.resolve();
        } else {
            saveFiltersCollection(equipments).then(function() {

                // get all filters with ID starting by __1
                var nbPersonsFilters = _.filter(data.filters, function(f) {
                    if (/^__\d+/.test(f.id)) {
                        return true;
                    }
                });

                if (!_.isEmpty(nbPersonsFilters)) {
                    saveFiltersCollection(nbPersonsFilters).then(function() {
                        deferred.resolve(equipments);
                    });
                }
            });
        }

        return deferred.promise;
    }

    /**
     * Save all filters, equipment before their children
     * @returns promise
     */
    function saveFilters() {
        var deferred = $q.defer();

        if (!isValidId(data.filters[0])) {

            // first create filter set
            var newFilterSet = {name: 'Sector : ' + data.geoname.name};
            Restangular.all('filterSet').post(newFilterSet).then(function(filterSet) {
                data.filters[0].filterSets = [filterSet];

                // then save first filter
                saveFiltersCollection([data.filters[0]]).then(function() {
                    updateUrl('filter');
                    saveEquipments().then(function() {
                        deferred.resolve();
                    });
                });
            });

        } else {
            return saveEquipments();
        }

        return deferred.promise;
    }

    /**
     * Avoid new questionnaires to have the same country for a same survey and avoid a same survey code to have two different years
     */
    function checkQuestionnairesIntegrity() {
        var deferred = $q.defer();

        // check for countries
        $timeout(function() {
            if (_.isEmpty(data.questionnaires)) {
                deferred.resolve();
            } else {
                _.forEach(data.questionnaires, function(q1, i) {
                    if (_.isUndefined(q1.id)) {
                        q1.errors = {
                            duplicateCountryCode: false,
                            codeAndYearDifferent: false,
                            countryAlreadyUsedForExistingSurvey: false
                        };

                        _.forEach(data.questionnaires, function(q2, j) {
                            if (_.isUndefined(q2.id) && i != j) {
                                if (q1.geoname && q2.geoname && q1.geoname.id == q2.geoname.id) {
                                    if (q1.survey.code && q2.survey.code && q1.survey.code == q2.survey.code) {
                                        q1.errors.duplicateCountryCode = true;
                                    }
                                } else {

                                    if (q1.survey.year && q2.survey.year && q1.survey.year != q2.survey.year && q1.survey.code == q2.survey.code) {
                                        q1.errors.codeAndYearDifferent = true;
                                    }
                                }
                            }
                        });
                    }
                    deferred.resolve();
                });
            }
        }, 0);

        return deferred.promise;
    }

    /**
     * This function recovers surveys by searching with Q params
     * If there is similar code, search if the country is already used
     * @param questionnaire
     * @returns null|survey Return null if no survey exists, returns the survey if exist or reject promise if country already used
     */
    function getSurvey(questionnaire) {
        var deferred = $q.defer();

        if (_.isUndefined(questionnaire.survey.id) && !_.isEmpty(questionnaire.survey.code)) {

            Restangular.all('survey').getList({q: questionnaire.survey.code, perPage: 1000, fields: 'questions,questions.filter,questionnaires,questionnaires.geoname'}).then(function(surveys) {
                if (surveys.length === 0) {
                    deferred.resolve({survey: null, questionnaire: null});
                } else {
                    var existingSurvey = _.find(surveys, function(s) {
                        if (s.code.toUpperCase() == questionnaire.survey.code.toUpperCase()) {
                            return true;
                        }
                    });

                    if (existingSurvey) {
                        // if wanted survey has no year, return found survey and questionnaire if found
                        if (_.isUndefined(questionnaire.survey.year) || !_.isUndefined(questionnaire.survey.year) && existingSurvey.year == questionnaire.survey.year) {

                            var existingQuestionnaire = null;
                            if (!_.isUndefined(questionnaire.geoname)) {
                                existingQuestionnaire = _.find(existingSurvey.questionnaires, function(q) {
                                    if (questionnaire.geoname.id == q.geoname.id) {
                                        return q;
                                    }
                                });
                            }

                            deferred.resolve({
                                survey: existingSurvey,
                                questionnaire: existingQuestionnaire
                            });

                            // if wanted survey has a different year, return an error
                        } else if (existingSurvey.year != questionnaire.survey.year) {
                            deferred.reject({code: 1, year: existingSurvey.year});
                        }

                        // else, there is not recoverable survey
                    } else {
                        deferred.resolve({survey: null, questionnaire: null});
                    }
                }
            });

        } else {
            deferred.resolve({survey: questionnaire.survey, questionnaire: questionnaire});
        }

        return deferred.promise;
    }

    /**
     * Get survey, or create it if dont exist
     * @param questionnaire
     */
    function getOrSaveSurvey(questionnaire) {
        var deferred = $q.defer();
        var survey = questionnaire.survey;

        getSurvey(questionnaire).then(function(data) {

            // same survey exists
            if (data.survey) {
                survey.id = data.survey.id;

                // init current questions id and names to match with those existing in the db
                _.forEach(data.survey.questions, function(question) {
                    if (!_.isUndefined(survey.questions[question.filter.id])) {
                        survey.questions[question.filter.id].id = question.id;
                        survey.questions[question.filter.id].name = question.name;
                    }
                });
                deferred.resolve(survey);

                // no survey exists, create it
            } else {
                survey.name = survey.code + " - " + survey.year;
                survey.isLoading = true;
                Restangular.all('survey').post(survey).then(function(newSurvey) {
                    survey.id = newSurvey.id;
                    survey.isLoading = false;
                    deferred.resolve(survey);
                });
            }

            // catch reject result
        }, function(error) {
            if (_.isUndefined(questionnaire.errors)) {
                questionnaire.errors = {};
            }
            if (error.code == 1) {
                questionnaire.errors.surveyExistWithDifferentYear = true;
                questionnaire.survey.existingYear = error.year;
            }
            deferred.reject();
        });

        return deferred.promise;
    }

    /**
     * Create a questionnaire object in database.
     * @param questionnaire
     * @returns promise
     */
    function getOrSaveUnitQuestionnaire(questionnaire) {
        var deferred = $q.defer();

        if (_.isUndefined(questionnaire.id)) {

            // create a mini questionnaire object, to avoid big amounts of data to be sent to server
            var miniQuestionnaire = {
                dateObservationStart: questionnaire.survey.year + '-01-01',
                dateObservationEnd: questionnaire.survey.year + '-12-31',
                geoname: questionnaire.geoname.id,
                survey: questionnaire.survey.id
            };

            questionnaire.isLoading = true;
            Restangular.all('questionnaire').post(miniQuestionnaire, {fields: 'permissions'}).then(function(newQuestionnaire) {
                questionnaire.id = newQuestionnaire.id;
                questionnaire.isLoading = false;
                deferred.resolve(questionnaire);
            });
        } else {
            deferred.resolve(questionnaire);
        }

        return deferred.promise;
    }

    /**
     * Set the value of a input (ng-model) before the value is changed
     * Used in function savePopulation().
     * Avoid to do some ajax requests when we just blur field without changing value.
     * @param model
     * @param value
     */
    function setInitialValue(model, value) {
        model.initialValue = value;
    }

    /**
     * Save valid new population data for the given questionnaire
     * @param questionnaire
     * @param population
     * @returns void
     */
    function savePopulation(questionnaire, population) {

        // Do nothing if the value did not change (avoid useless ajax)
        if (population.initialValue === population.population || !questionnaire.survey || !questionnaire.id || !questionnaire.geoname) {
            return;
        }
        setInitialValue(population, population.population);
        Restangular.restangularizeElement(null, population, 'population');

        // Be sure that population data are in sync with other data from questionnaire/survey
        population.questionnaire = questionnaire.id;
        population.year = questionnaire.survey.year;
        population.geoname = questionnaire.geoname.id;

        // If new population with value, create it
        if (_.isUndefined(population.id) && _.isNumber(population.population)) {

            population.isLoading = true;
            Restangular.all('population').post(population).then(function(newPopulation) {
                population.id = newPopulation.id;
                population.isLoading = false;
            });
        } else if (population.id && _.isNumber(population.population)) {
            // If existing population with new value, update it
            population.isLoading = true;
            population.put().then(function() {
                population.isLoading = false;
            });
        } else if (population.id && !_.isNumber(population.population)) {
            // If existing population with deleted value, delete it
            population.isLoading = true;
            population.remove().then(function() {
                delete population.id;
                population.isLoading = false;
            });
        }

        refresh(false, true, false);
    }

    /**
     * use question or save if necessary and return result
     * @param question
     */
    function getOrSaveQuestion(question) {
        var deferred = $q.defer();

        if (!isValidId(question.filter)) {
            deferred.reject();
            return deferred.promise;
        }

        // if no id, create
        if (_.isUndefined(question.id)) {
            var miniQuestion = {};
            miniQuestion.name = question.name;
            miniQuestion.survey = question.survey;
            miniQuestion.filter = question.filter.id;
            miniQuestion.type = 'Numeric';
            miniQuestion.isAbsolute = question.isAbsolute;
            miniQuestion.alternateNames = question.alternateNames;

            question.isLoading = true;
            Restangular.all('question').post(miniQuestion).then(function(newQuestion) {
                question.id = newQuestion.id;
                question.isLoading = false;
                deferred.resolve(question);
            });

            // else, do nothing
        } else {
            deferred.resolve(question);
        }

        return deferred.promise;
    }

    /**
     * Update answers considering answer permissions
     * @param answer
     * @param questionnaire
     */
    function updateAnswer(answer, questionnaire) {
        var deferred = $q.defer();
        if (answer.id && (answer.permissions && answer.permissions.update || !answer.permissions && questionnaire && questionnairesStatus[questionnaire.status])) {
            Restangular.restangularizeElement(null, answer, 'answer');

            delete(answer.error);
            answer.isLoading = true;
            answer.put().then(function(savedAnswer) {
                answer.isLoading = false;
                if (!_.isUndefined(savedAnswer.valuePercent)) {
                    answer.valuePercent = savedAnswer.valuePercent;
                }
                if (!_.isUndefined(savedAnswer.valueAbsolute)) {
                    answer.valueAbsolute = savedAnswer.valueAbsolute;
                }
                deferred.resolve();
                refresh(false, true, false);
            }, function(data) {
                answer.error = data;
                deferred.reject();
            });
        } else {
            deferred.resolve();
        }

        return deferred.promise;
    }

    /**
     * Create answer considering *questionnaire* permissions
     * @param answer
     * @param question
     * @param params
     */
    function createAnswer(answer, question, params) {
        var deferred = $q.defer();

        if (_.isUndefined(params)) {
            params = {};
        }

        if (answer[question.value] || answer[question.value] === 0) {

            // init part if missing
            if (!answer.part && params.part) {
                answer.part = params.part.id;
            }

            // init questionnaire if missing
            if (!answer.questionnaire && params.questionnaire) {
                answer.questionnaire = params.questionnaire.id;
            }

            // init question if missing
            if (!answer.question && question) {
                answer.question = question.id;
            }

            answer.isLoading = true;
            delete(answer.error);
            Restangular.all('answer').post(answer, {fields: 'permissions'}).then(function(newAnswer) {
                answer.id = newAnswer.id;
                answer[question.value] = newAnswer[question.value];
                answer.displayValue = question.isAbsolute ? newAnswer[question.value] : Percent.fractionToPercent(newAnswer[question.value]);
                answer.isLoading = false;
                deferred.resolve(answer);
            }, function(data) {
                answer.error = data;
                answer.isLoading = false;
            });
        } else {
            deferred.reject();
        }

        return deferred.promise;
    }

    /**
     * Update an answer
     * Create an answer and if needed the question related
     * @param answer
     * @param question
     * @param filter
     * @param questionnaire
     * @param part
     */
    function saveAnswer(answer, question, filter, questionnaire, part) {

        var deferred = $q.defer();

        // complete question in all situations with filter name if there is no name specified
        if (_.isUndefined(question.name) && !_.isUndefined(filter)) {
            question.name = filter.name;
        }

        // if the value is not changed or if it's invalid (undefined), reset to initial value
        var valueToBeSaved = question.isAbsolute ? answer.displayValue : Percent.percentToFraction(answer.displayValue);
        if (answer.initialValue === valueToBeSaved || !_.isNumber(valueToBeSaved) && !_.isUndefined(answer.initialValue)) {
            answer.displayValue = question.isAbsolute ? answer.initialValue : Percent.fractionToPercent(answer.initialValue);
            deferred.resolve();
            return deferred.promise;
        }

        // avoid to save questions when its a new questionnaire / survey
        // the save is provided by generate button for all new questionnaires, surveys, questions and answers.
        if (_.isUndefined(questionnaire.id)) {
            deferred.resolve();
            return deferred.promise;
        }

        // If we reached this far, then we will touch the server, so we prepare the answer
        answer[question.value] = valueToBeSaved;
        Restangular.restangularizeElement(null, answer, 'answer');
        Restangular.restangularizeElement(null, question, 'question');

        // delete answer if no value
        if (answer.id && !Utility.isValidNumber(valueToBeSaved)) {
            deleteAnswer(question, answer);
            deferred.resolve();

            // update
        } else if (answer.id) {

            getPermissions(question, answer).then(function() {
                updateAnswer(answer, questionnaire).then(function() {
                    answer.initialValue = valueToBeSaved;
                    answer.displayValue = question.isAbsolute ? answer[question.value] : Percent.fractionToPercent(answer[question.value]);
                    deferred.resolve(answer);
                });
            });

            // create answer, if allowed by questionnaire
        } else if (_.isUndefined(answer.id) && Utility.isValidNumber(valueToBeSaved) && questionnairesStatus[questionnaire.status]) {
            answer.questionnaire = questionnaire.id;
            question.survey = questionnaire.survey.id;

            // if question is not created, create it before creating the answer
            getOrSaveQuestion(question).then(function(question) {
                createAnswer(answer, question, {part: part}).then(function() {
                    answer.initialValue = valueToBeSaved;
                    answer.displayValue = question.isAbsolute ? answer[question.value] : Percent.fractionToPercent(answer[question.value]);
                    deferred.resolve(answer);
                    refresh(false, true, false);
                });
            });
        }

        return deferred.promise;
    }

    /**
     * Multiple questionnaires may have the same survey, but the questions are linked to questionnaires with the right answers.
     * So questions may be in multiple questionnaires but they have to be synced for labels and id. A filter is supposed to have only one question.
     * This function propagates modifications on other questionnaires that have the same code.
     * @param survey the source of the question
     * @param {boolean} propagateId
     * @param {boolean} propagateName
     */
    function propagateQuestions(survey, propagateId, propagateName) {

        if (survey.code && survey.questions) {
            var questionnairesWithSameLabel = getSurveysWithSameCode(data.questionnaires, survey.code);

            _.forEach(questionnairesWithSameLabel, function(questionnaire) {
                _.forEach(survey.questions, function(question) {
                    if (survey.id && questionnaire.survey.questions[question.filter.id]) {
                        questionnaire.survey.questions[question.filter.id].survey = survey.id;
                    }
                    if (questionnaire.survey.questions && questionnaire.survey.questions[question.filter.id]) {
                        if (propagateId && question.id) {
                            questionnaire.survey.questions[question.filter.id].id = question.id;
                        }
                        if (propagateName && !_.isEmpty(question.name)) {
                            questionnaire.survey.questions[question.filter.id].name = question.name;
                        }
                    }
                });
            });
        }
    }

    /**
     * When recovering data from BD, propagates this data on survey that have the same code.
     * @param survey
     */
    function propagateSurvey(survey) {
        _.forEach(getSurveysWithSameCode(data.questionnaires, survey.code), function(questionnaire) {
            questionnaire.survey.id = survey.id;
            propagateQuestions(survey, true, true);
        });
    }

    /**
     * Create a questionnaire, recovering or creating related survey and questions
     * @param questionnaire
     */
    function saveCompleteQuestionnaire(questionnaire) {
        var Qdeferred = $q.defer();

        // get survey if exists or create
        getOrSaveSurvey(questionnaire).then(function(survey) {
            questionnaire.survey = survey;

            // create questionnaire
            getOrSaveUnitQuestionnaire(questionnaire).then(function(newQuestionnaire) {

                questionnaire.id = newQuestionnaire.id;
                propagateSurvey(survey);
                updateUrl('questionnaires');

                // Save all populations
                angular.forEach(questionnaire.populations, function(population) {
                    savePopulation(questionnaire, population);
                });

                // create questions
                var questionsForSave = _.filter(questionnaire.survey.questions, function(q) {
                    if (q.name) {
                        return true;
                    }
                });

                if (questionsForSave.length === 0) {
                    Qdeferred.notify();
                    Qdeferred.resolve(questionnaire);
                } else {
                    var answersPromises = [];
                    var questionsPromises = _.map(questionsForSave, function(question) {
                        var qdeferred = $q.defer();
                        getOrSaveQuestion(question).then(function(newQuestion) {
                            question = newQuestion;
                            propagateSurvey(survey);

                            _.forEach(question.answers, function(answer) {
                                answersPromises.push(saveAnswer(answer, question, undefined, questionnaire));
                            });

                            qdeferred.resolve(question);
                        });
                        return qdeferred.promise;
                    });

                    // once all questions have been saved, notify next questionnaire to start saving
                    // and add listener on all answers promises to notify end of full saved questionnaire
                    $q.all(questionsPromises).then(function() {
                        propagateSurvey(survey);
                        $q.all(answersPromises).then(function() {
                            Qdeferred.resolve(questionnaire);
                        });
                        Qdeferred.notify('Questions recovered'); // says to promise listener he can save next questionnaire (notification)
                    });
                }
            });

            // reject for survey creation
        }, function() {
            questionnaire.isLoading = false;
        });

        return Qdeferred.promise;
    }

    /**
     * Save question if it has a name
     * @param question
     * @param questionnaire
     */
    function saveQuestion(question, questionnaire) {

        var deferred = $q.defer();

        // If the question is new and does not have an official name use it the alternative as real one
        var alternateName = question.alternateNames[questionnaire.id];
        if (!question.name) {
            question.name = alternateName;
        }

        // If we deleted the alternative name, or it is the same as official one, delete the alternative
        if (!alternateName || alternateName === question.name) {
            delete question.alternateNames[questionnaire.id];
        }

        propagateQuestions(questionnaire.survey, false, true);

        if (question.id) {
            Restangular.restangularizeElement(null, question, 'question');
            question.isLoading = true;
            question.put().then(function() {
                propagateQuestions(questionnaire.survey, true, false);
                question.isLoading = false;

                if (!question.alternateNames[questionnaire.id]) {
                    question.alternateNames[questionnaire.id] = question.name;
                }

                deferred.resolve();

            });
        } else {
            return getOrSaveQuestion(question);
        }

        return deferred.promise;
    }

    function getFiltersByLevel(level, filters) {
        filters = _.isUndefined(filters) ? data.filters : filters;
        return _.filter(filters, function(filter) {
            if (filter.level == level) {
                return true;
            }
            return false;
        });
    }

    /**
     * Get all children and children's children, by searching in parents references
     * @param filter
     * @returns {Array}
     */
    function getChildrenRecursively(filter) {
        var children = _.filter(data.filters, function(f) {
            if (_.find(f.parents, {id: filter.id})) {
                return true;
            }
        });

        _.forEach(children, function(f) {
            children = children.concat(getChildrenRecursively(f));
        });

        return children;
    }

    /**
     * Get immediate children, by searching in parents references
     * @param filter
     * @returns {Array}
     */
    function getChildren(filter) {
        var children = _.filter(data.filters, function(f) {
            if (_.find(f.parents, {id: filter.id})) {
                return true;
            }
        });

        return children;
    }

    /**
     * Create usages only if we are in sector mode
     * As we cancel sector mode at
     * @param questionnaire
     * @param filters
     */
    function createQuestionnaireFilterUsages(questionnaire, filters) {
        if (data.mode.isSector) {

            var deferred = $q.defer();

            var equipments = _.map(getFiltersByLevel(1, filters), function(filter) {
                return filter.id + ':' + _.pluck(getChildren(filter), 'id').join('-');
            });

            questionnaire.filterQuestionnaireUsages = 1;

            $http.get('/api/filter/createUsages', {
                params: {
                    filters: equipments.join(','),
                    questionnaires: questionnaire.id
                }
            }).success(function() {
                deferred.resolve();
            });

            return deferred.promise;
        }
    }

    /**
     * Save given questionnaires and create sector rules for given filters (rules only if sector mode)
     * This method regroup questionnaires by survey, then save first questionnaire of each survey and his questions
     * Then, when questions have been saved, they're propagated to other questionnaires from same survey in client side
     * Then, other questionnaires are saved, without questions
     * Answers are always saved if needed
     * @param questionnairesToSave
     * @param savedFacilities
     */
    function saveQuestionnaires(questionnairesToSave, savedFacilities) {

        var questionnairesToSaveBySurvey = _.groupBy(questionnairesToSave, function(q) {
            return q.survey.code;
        });

        var savedQuestionnaires = 0;

        _.forEach(questionnairesToSaveBySurvey, function(questionnaires) {
            // save the first questionnaire (and his questions)
            var questionnaire = questionnaires.shift();
            if (!questionnaire.id) {
                savedFacilities = getFiltersByLevel(1);
            }

            saveCompleteQuestionnaire(questionnaire).then(function(questionnaire) {
                createQuestionnaireFilterUsages(questionnaire, savedFacilities).then(function() {
                    savedQuestionnaires++;
                    if (savedQuestionnaires == questionnairesToSave.length) {
                        refresh(false, true, true);
                    }
                });
            }, function() {
            }, function() {
                // notification when questions have been saved
                // then, once the questions have been created, save all other questionnaires
                _.forEach(questionnaires, function(questionnaire) {
                    if (!questionnaire.id) {
                        savedFacilities = getFiltersByLevel(1);
                    }
                    saveCompleteQuestionnaire(questionnaire).then(function(questionnaire) {
                        createQuestionnaireFilterUsages(questionnaire, savedFacilities).then(function() {
                            savedQuestionnaires++;
                            if (savedQuestionnaires == questionnairesToSave.length) {
                                refresh(false, true, true);
                            }
                        });
                    });
                });
            });
        });
    }

    /**
     * Save one questionnaire if specified or all if it's not
     * @param questionnaire
     */
    function saveAll(questionnaire) {

        checkQuestionnairesIntegrity().then(function() {
            saveFilters().then(function(savedFacilities) {
                var questionnairesToCreate = !_.isUndefined(questionnaire) ? [questionnaire] : _.filter(data.questionnaires, questionnaireCanBeSaved);
                var existingQuestionnaires = _.filter(data.questionnaires, 'id');
                saveQuestionnaires(questionnairesToCreate.concat(existingQuestionnaires), savedFacilities);
            });
        });
    }

    /**
     * Check if questionnaire has one or multiple errors
     * @param questionnaire
     * @returns {boolean}
     */
    function questionnaireHasErrors(questionnaire) {
        var hasErrors = false;
        _.forEach(questionnaire.errors, function(potentialError) {
            if (potentialError) {
                hasErrors = true;
                return false;
            }
        });

        return hasErrors;
    }

    /**
     * Return if a questionnaire is ready to be saved (has code, year, geoname and no errors
     * @param questionnaire
     * @returns {boolean}
     */
    function questionnaireCanBeSaved(questionnaire) {
        return _.isUndefined(questionnaire.id) && !_.isUndefined(questionnaire.geoname) && !_.isUndefined(questionnaire.survey) && !_.isEmpty(questionnaire.survey.code) && !_.isUndefined(questionnaire.survey.year) && !questionnaireHasErrors(questionnaire);
    }

    /**
     * Recovers existing data from DB when an existing code/year/country are set in unsaved questionnaire
     * @param questionnaire
     */
    function completeQuestionnaire(questionnaire) {
        if (_.isUndefined(questionnaire.id) && questionnaire.survey) {
            getSurvey(questionnaire).then(function(data) {
                if (data.survey && data.survey.year) {
                    questionnaire.survey.year = data.survey.year;
                    propagateQuestions(data.survey, true, true);
                }

                if (data.questionnaire && data.questionnaire.id) {
                    questionnaire.id = data.questionnaire.id;
                    questionnaire.survey.id = data.survey.id;
                    getQuestionnaires([data.questionnaire.id], questionnaireWithAnswersFields).then(function(questionnaires) {
                        prepareDataQuestionnaires(questionnaires);
                        updateUrl('questionnaires');
                    });
                }

            }, function(error) {
                if (_.isUndefined(questionnaire.errors)) {
                    questionnaire.errors = {};
                }
                if (error.code == 1) {
                    questionnaire.errors.surveyExistWithDifferentYear = true;
                    questionnaire.survey.existingYear = error.year;
                }
            });
        }
    }

    /**
     * Check all questionnaires integrity and then complete the given questionnaire if survey exists in DB
     * @param {type} questionnaire
     */
    function checkAndCompleteQuestionnaire(questionnaire) {
        checkQuestionnairesIntegrity();
        completeQuestionnaire(questionnaire);
    }

    /**
     * In case sector parameter is detected in url, this function ensures each filter has
     * sub filters dedicated to filter data (Usually people and equipment but may be anything)
     */
    function prepareSectorFilters() {
        if (data.mode.isSector && data.filters) {

            var equipments = _.filter(data.filters, function(f) {
                if (!isValidId(f) && f.level == 1) {
                    return true;
                }
                return false;
            });

            var sectorChildrenNames = ['Number of facilities', 'Persons per facilities'];

            _.forEachRight(equipments, function(equipment) {
                // get equipment children filter
                var equipementData = _.filter(data.filters, function(f) {
                    if (_.find(f.parents, {id: equipment.id})) {
                        return true;
                    }
                });

                // if no children added to equipment yet, add it
                if (equipementData.length === 0) {
                    var sectorChildFilters = _.map(sectorChildrenNames, function(childSectorName, sectorIndex) {
                        return {
                            id: '_' + equipment.id + "_" + sectorIndex,
                            name: childSectorName,
                            parents: [
                                {id: equipment.id}
                            ],
                            level: 2
                        };
                    });

                    // add children to equipments
                    var index = _.findIndex(data.filters, {id: equipment.id});
                    _.forEach(sectorChildFilters, function(filter, sectorIndex) {
                        data.filters.splice(index + sectorIndex + 1, 0, filter);
                    });
                }
            });
            fillMissingElements();
        }
    }

    /**
     * Add a filter to list
     * Filters that have been added must have and ID because the questions are indexed on their filter id,
     * This function assigns an arbitrary ID starting with and underscore that is replaced on save.
     * This underscored id is used for children filters that need a reference to parents.
     */
    function addEquipment() {
        if (data.filter) {
            if (_.isUndefined(data.filters)) {
                data.filters = [];
            }
            data.filters.push({
                id: "_" + lastUnsavedFilterId++,
                level: 1,
                parents: [
                    {id: data.filter.id}
                ]
            });
            fillMissingElements();
        }
    }

    var addSectorFilterSet = function() {
        data.filter = {
            id: '_' + lastUnsavedFilterId++,
            name: 'Sector : ' + data.geoname.name,
            level: 0,
            color: '#FF0000'
        };
        $timeout(addEquipment, 0);
    };

    /**
     * Add column (questionnaire)
     */
    function addQuestionnaire() {
        if (_.isUndefined(data.questionnaires)) {
            data.questionnaires = [];
        }

        var questionnaire = {};
        if (data.geoname) {
            questionnaire.geoname = _.cloneDeep(data.geoname);
        }

        data.questionnaires.splice(0, 0, questionnaire);
        fillMissingElements();
        updateUrl('questionnaires');
    }

    var initSector = function() {
        if (data.mode.isSector) {
            data.filter = undefined;
            data.filters = [];
            updateUrl('questionnaires');
            updateUrl('filter');
            updateUrl('filters');

            if (data.geoname) {

                $http.get('/api/filter/getSectorFiltersForGeoname', {
                    params: {
                        geoname: data.geoname.id
                    }
                }).success(function(loadedSectorFilter) {
                    if (loadedSectorFilter.id) {
                        data.filter = loadedSectorFilter;
                    } else {
                        addSectorFilterSet();
                    }
                });
            }
        }
    };

    function loadGeoname() {
        var deferred = $q.defer();
        data.questionnaires = [];
        Restangular.one('geoname', data.geoname.id).getList('questionnaire', _.merge(questionnaireFields, {surveyType: data.mode.surveyType, perPage: 1000})).then(function(questionnaires) {
            data.questionnaires = questionnaires;
            data.survey = null;
            initSector();
            deferred.resolve();
        });

        return deferred.promise;
    }

    function loadSurvey() {
        var deferred = $q.defer();
        data.questionnaires = [];
        Restangular.one('survey', data.survey.id).getList('questionnaire', _.merge(questionnaireFields, {surveyType: data.mode.surveyType, perPage: 1000})).then(function(questionnaires) {
            data.questionnaires = questionnaires;
            data.geoname = null;
            deferred.resolve();
        });

        return deferred.promise;
    }

    function removeQuestionsForFilter(id, isRegex) {
        _.forEach(data.questionnaires, function(questionnaire) {
            // only remove questions on new questionnaires, others have received data from DB and shouldn't be removed
            if (_.isUndefined(questionnaire.id)) {
                _.forEach(questionnaire.survey.questions, function(question, filterId) {
                    if (isRegex && id.test(filterId) || !isRegex && id == filterId) {
                        delete(questionnaire.survey.questions[filterId]);
                    }
                });
            }
        });
    }

    /**
     * When removing a filter, this function remove related questions on questionnaires to ensure no unwanted operation on DB is made
     * @param newFilters
     * @param oldFilters
     */
    function removeUnUsedQuestions(newFilters, oldFilters) {
        var removedFilters = _.difference(_.pluck(oldFilters, 'id'), _.pluck(newFilters, 'id'));
        _.forEach(removedFilters, function(filterId) {
            removeQuestionsForFilter(filterId, false);
        });
    }

    /**
     * Remove row (filter)
     * @param filter
     */
    function removeFilter(filter) {

        var filters = _.uniq(_.pluck([filter].concat(getChildrenRecursively(filter)), 'id'));

        // remove filter if it's in list of
        data.filters = _.filter(data.filters, function(f) {
            if (!_.contains(filters, f.id)) {
                return true;
            }
            return false;
        });

        updateUrl('filters');

        _.forEach(filters, function(f) {
            removeQuestionsForFilter(f.id, false);
        });
    }

    function loadFilter(newFilters, oldFilters) {
        var deferred = $q.defer();
        removeUnUsedQuestions(newFilters, oldFilters);
        fillMissingElements();
        getComputedFilters();
        deferred.resolve();

        return deferred.promise;
    }

    /**
     * Toggle showLabels for all questionnaire, or only the one specified
     * @param {type} questionnaire
     * @returns {undefined}
     */
    function toggleShowLabels(questionnaire) {
        if (questionnaire) {
            questionnaire.showLabels = !questionnaire.showLabels;
            setQuestionnaireWidth(questionnaire);
        } else {
            var firstQuestionnaireStatus = !data.questionnaires[0].showLabels;
            _.forEach(data.questionnaires, function(questionnaire) {
                questionnaire.showLabels = firstQuestionnaireStatus;
                setQuestionnaireWidth(questionnaire);
            });
        }

        $rootScope.$broadcast('vsRepeatTriggering');
        $rootScope.$emit('gims-tablefilter-show-labels-toggled');
    }

    var filtersSection = null;
    var questionnairesSection = null;
    var questionnairesHeaderSection = null;

    function adjustHeight() {
        filtersSection = jQuery("#filtersSection");
        questionnairesSection = jQuery("#questionnairesSection");
        questionnairesHeaderSection = jQuery("#questionnairesHeaderSection");
        resizeContent();
        jQuery(window).resize(resizeContent);
    }

    function resizeContent() {
        var headerHeight = jQuery("#header").outerHeight(true);
        var footerHeight = jQuery("#footer").outerHeight(true);
        var toolsHeight = jQuery("#tools").outerHeight(true);
        var margin = 275;

        if (data.mode.isSector) {
            margin += 46;
        }

        var contentHeight = jQuery(window).height() - headerHeight - footerHeight - toolsHeight - margin;

        filtersSection.height(contentHeight);
        questionnairesSection.height(contentHeight);
    }

    function syncScroll() {
        filtersSection.scroll(function(e) {
            questionnairesSection.scrollTop(e.target.scrollTop);
        });
        questionnairesSection.scroll(function(e) {
            filtersSection.scrollTop(e.target.scrollTop);
            questionnairesHeaderSection.scrollLeft(e.target.scrollLeft);
        });
        questionnairesHeaderSection.scroll(function(e) {
            questionnairesSection.scrollLeft(e.target.scrollLeft);
        });
    }

    // Return public API
    return {
        init: init,
        addEquipment: addEquipment,
        addQuestionnaire: addQuestionnaire,
        addSectorFilterSet: addSectorFilterSet,
        checkAndCompleteQuestionnaire: checkAndCompleteQuestionnaire,
        getCellType: getCellType,
        getData: getData,
        getFiltersByLevel: getFiltersByLevel,
        getPermissions: getPermissions,
        getSurveysWithSameCode: getSurveysWithSameCode,
        initQuestionAbsolute: initQuestionAbsolute,
        isValidId: isValidId,
        loadFilter: loadFilter,
        loadGeoname: loadGeoname,
        loadQuestionnaires: loadQuestionnaires,
        loadSurvey: loadSurvey,
        prepareSectorFilters: prepareSectorFilters,
        questionnaireCanBeSaved: questionnaireCanBeSaved,
        refresh: refresh,
        deleteAnswer: deleteAnswer,
        removeFilter: removeFilter,
        saveAll: saveAll,
        saveAnswer: saveAnswer,
        savePopulation: savePopulation,
        saveQuestion: saveQuestion,
        setInitialValue: setInitialValue,
        toggleShowLabels: toggleShowLabels,
        toggleShowQuestionnaireUsages: toggleShowQuestionnaireUsages,
        updateUrl: updateUrl,
        adjustHeight: adjustHeight,
        syncScroll: syncScroll,
        resizeContent: resizeContent
    };
});
