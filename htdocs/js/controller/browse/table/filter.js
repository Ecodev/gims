angular.module('myApp').controller('Browse/FilterCtrl', function($scope, $location, $http, $timeout, Restangular, $q, $rootScope, requestNotification, $filter) {
    'use strict';

    /**************************************************************************/
    /*********************************************** Variables initialisation */
    /**************************************************************************/

    // My future self will hate me for this, but we hardcode the exclude
    // rule ID to make it easier to find it
    var excludeRuleId = 1;

    // params for ajax requests
    $scope.filterFields = {fields: 'color,paths,parents,summands'};
    $scope.countryParams = {fields: 'geoname'};
    var countryFields = {fields: 'geoname.questionnaires,geoname.questionnaires.survey,geoname.questionnaires.survey.questions,geoname.questionnaires.survey.questions.type,geoname.questionnaires.survey.questions.filter'};
    var questionnaireWithQTypeFields = {fields: 'status,survey.questions,survey.questions.type,survey.questions.isAbsolute'};
    var questionnaireWithAnswersFields = {fields: 'status,filterQuestionnaireUsages,permissions,comments,geoname.country,survey.questions,survey.questions.isAbsolute,survey.questions.filter,survey.questions.alternateNames,survey.questions.answers.questionnaire,survey.questions.answers.part,populations.part'};
    var surveyFields = {fields: 'questionnaires.status,questionnaires.survey,questionnaires.survey.questions,questionnaires.survey.questions.type,questionnaires.survey.questions.filter'};

    // Variables initialisations
    $scope.expandHierarchy = true;
    $scope.expandSelection = true;
    $scope.lastFilterId = 1;
    $scope.firstQuestionnairesRetrieve = false; // avoid to compute filters before questionnaires have been retrieved, getComputedFilters() need ready base to complete data
    $scope.tabs = {};
    $scope.sectorChildren = 'Number of facilities,Persons per facilities';
    $scope.parts = Restangular.all('part').getList().$object;
    $scope.surveysTemplate = "[[item.code]] - [[item.name]]";
    $scope.questionnairesStatus = {
        validated: false,
        published: false,
        completed: true,
        rejected: true,
        'new': true
    };

    $scope.modes = [
        {
            name: 'Browse',
            isContribute: false,
            isSector: false
        },
        {
            name: 'Contribute JMP',
            isContribute: true,
            isSector: false
        },
        {
            name: 'Contribute NSA',
            isContribute: true,
            isSector: true
        }
    ];

    /**************************************************************************/
    /***************************************** First execution initialisation */
    /**************************************************************************/

    $scope.locationPath = $location.$$path;

    if ($location.$$path.indexOf('/nsa') >= 0) {
        $scope.mode = $scope.modes[2];
    } else if ($location.$$path.indexOf('/contribute') >= 0) {
        $scope.mode = $scope.modes[1];
    } else if ($location.$$path.indexOf('/browse') >= 0) {
        $scope.mode = $scope.modes[0];
    }

    /**************************************************************************/
    /*************************************************************** Watchers */
    /**************************************************************************/

    // Subscribe to listen when there is network activity
    $scope.isLoading = false;
    requestNotification.subscribeOnRequest(function() {
        $scope.isLoading = true;
    }, function() {
        if (requestNotification.getRequestCount() === 0) {
            $scope.isLoading = false;
        }
    });

    $scope.$watch(function() {
        return $location.url();
    }, function() {
        $scope.returnUrl = $location.search().returnUrl;
        $scope.currentUrl = encodeURIComponent($location.url());
    });

    $scope.$watch('mode', function(mode) {
        if (!_.isUndefined(mode) && mode != 'Browse') {
            // Make a call that require to be authenticated, then UserCtrl catch 401 and fire event gims-loginConfirmed when it's done
            Restangular.all('user').getList();
            // listen to event gims-loginConfirmed to refresh questionnaires permissions, considering logged in user
            $rootScope.$on('gims-loginConfirmed', function() {
                $scope.refresh(true, false);
            });

        }
    });

    $scope.$watch('tabs.filterSet', function() {
        if ($scope.tabs.filterSet) {
            $scope.tabs.filters = [];
            Restangular.one('filterSet', $scope.tabs.filterSet.id).getList('filters', _.merge($scope.filterFields, {perPage: 1000})).then(function(filters) {
                if (filters) {
                    $scope.tabs.filters = filters;
                    $scope.tabs.filter = null;
                    updateUrl('filter');
                }
                checkSelectionExpand();
            });
        }
    });

    $scope.$watch('tabs.filter', function() {
        if ($scope.tabs.filter) {
            Restangular.one('filter', $scope.tabs.filter.id).getList('children', _.merge($scope.filterFields, {perPage: 1000})).then(function(filters) {
                if (filters) {

                    // Inject parent as first filter, so we are able to see the "main" value
                    _.forEach(filters, function(filter) {
                        filter.level++;
                    });
                    var parent = _.clone($scope.tabs.filter);
                    parent.level = 0;
                    filters.unshift(parent);

                    $scope.tabs.filters = filters;
                    $scope.tabs.filterSet = null;
                    updateUrl('filterSet');

                }
                checkSelectionExpand();
            });
        }
    });

    $scope.$watch('tabs.country', function() {
        if ($scope.tabs.country) {
            $scope.tabs.questionnaires = [];
            Restangular.one('country', $scope.tabs.country.id).get(_.merge(countryFields, {perPage: 1000})).then(function(country) {
                $scope.tabs.questionnaires = country.geoname.questionnaires;
                $scope.tabs.survey = null;
                checkSelectionExpand();
            });
        }
    });

    $scope.$watch('tabs.survey', function() {
        if ($scope.tabs.survey) {
            $scope.tabs.questionnaires = [];
            Restangular.one('survey', $scope.tabs.survey.id).get(_.merge(surveyFields, {perPage: 1000})).then(function(survey) {
                $scope.tabs.questionnaires = survey.questionnaires;
                $scope.tabs.country = null;
                checkSelectionExpand();
            });
        }
    });

    var firstLoading = true;
    $scope.$watch('tabs.filters', function(newFilters, oldFilters) {
        removeUnUsedQuestions(newFilters, oldFilters);
        fillMissingElements();
        getComputedFilters();
        if (firstLoading === true && $scope.tabs.filters && $scope.tabs.questionnaires) {
            checkSelectionExpand();
        }
        if ($scope.tabs.filters) {
            validateNSAFilterStructure();
        }
    });

    $scope.$watchCollection('tabs.filters', function() {
        prepareSectorFilters();
    });

    $scope.$watch('tabs.questionnaires', function(newQuests, oldQuests) {
        var newQuestionnaires = _.difference(_.pluck(newQuests, 'id'), _.pluck(oldQuests, 'id'));
        newQuestionnaires = newQuestionnaires ? newQuestionnaires : [];

        if (!_.isEmpty(newQuestionnaires)) {
            getQuestionnaires(newQuestionnaires, questionnaireWithQTypeFields).then(function(questionnaires) {
                checkGlassQuestionnaires(questionnaires);
                $scope.orderQuestionnaires(false);
            });
        }

        if (firstLoading === true && $scope.tabs.filters && $scope.tabs.questionnaires) {
            checkSelectionExpand();
        }
    });

    /**************************************************************************/
    /******************************************************** Scope functions */
    /**************************************************************************/

    $scope.setMode = function(i) {
        $scope.mode = $scope.modes[i];
    };

    /**
     * Refreshing page means :
     *  - Recover all questionnaires permissions (in case user switch from browse to contribute/full view and need to be logged in)
     *  - Recompute filters, after some changes on answers. Can be done automatically after each answer change, but is heavy.
     * @param questionnairesPermissions
     * @param filtersComputing
     */
    $scope.refresh = function(questionnairesPermissions, filtersComputing) {

        if (questionnairesPermissions && !_.isUndefined($scope.tabs) && !_.isUndefined($scope.tabs.questionnaires)) {
            getQuestionnaires($scope.tabs.questionnaires, {fields: 'permissions'}).then(function(questionnaires) {
                updateQuestionnairePermissions(questionnaires);
            });
        }

        if (filtersComputing) {
            getComputedFilters();
        }
    };

    $scope.orderQuestionnaires = function(reverse) {
        $scope.tabs.questionnaires = $filter('orderBy')($scope.tabs.questionnaires, 'survey.year', reverse);
        $scope.questionnairesAreSorted = true;
    };

    /**
     * Call api to get answer permissions
     * @param question
     * @param answer
     * @param questionnaire
     */
    $scope.getPermissions = function(question, answer, questionnaire) {

        var deferred = $q.defer();

        if (answer.id && _.isUndefined(answer.permissions)) {
            Restangular.one('answer', answer.id).get({fields: 'permissions'}).then(function(newAnswer) {
                answer.permissions = newAnswer.permissions;

                // if value has been updated between permissions check, restore value
                if (!answer.permissions.update) {
                    answer[question.value] = newAnswer[question.value];
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
                create: $scope.questionnairesStatus[questionnaire.status],
                read: $scope.questionnairesStatus[questionnaire.status],
                update: $scope.questionnairesStatus[questionnaire.status],
                delete: $scope.questionnairesStatus[questionnaire.status]
            };
        }

        return deferred.promise;
    };

    /**
     * Detect if there are empty questionnaires to display button "generate"
     * @returns {boolean}
     */
    $scope.isEmptyQuestionnaires = function() {
        var isEmptyQuestionnaires = false;
        _.forEachRight($scope.tabs.questionnaires, function(questionnaire) {
            if (_.isUndefined(questionnaire.id)) {
                isEmptyQuestionnaires = true;
                return false;
            }
        });
        return isEmptyQuestionnaires;
    };

    /**
     * Detect if there are empty filters to display button "generate"
     * @returns {boolean}
     */
    $scope.isEmptyFilters = function() {
        var filters = _.filter($scope.tabs.filters, function(f) {
            if (/^_.*/.test(f.id)) {
                return true;
            }
        });
        if (filters.length) {
            return true;
        } else {
            return false;
        }
    };

    /**
     * If a question has no value for isAbsolute attribute, set is by percent by default
     * @todo adapt to sector, default to absolute and replace everywhere where those values are setted
     * @param question
     */
    $scope.initQuestionAbsolute = function(question) {

        // if absolute is undefined and we are in contribute view (no sector) -> set question as percent by default
        if (_.isUndefined(question.isAbsolute) && !$scope.mode.isSector || question && question.isAbsolute === false) {
            question.isAbsolute = false;
            question.value = 'valuePercent';
            question.max = 100;

            // if absolute is undefined and we are in sector view -> set question as absolute by default
        } else if (_.isUndefined(question.isAbsolute) && $scope.mode.isSector || question && question.isAbsolute === true) {
            question.isAbsolute = true;
            question.value = 'valueAbsolute';
            question.max = 100000000000;
        }

        return question;
    };

    /**
     * Update an answer
     * Create an answer and if needed the question related
     * @param answer
     * @param question
     * @param filter
     * @param questionnaire
     * @param part
     */
    $scope.saveAnswer = function(answer, question, filter, questionnaire, part) {

        var deferred = $q.defer();

        // complete question in all situations with filter name if there is no name specified
        if (_.isUndefined(question.name) && !_.isUndefined(filter)) {
            question.name = filter.name;
        }

        // avoid to do some job if the value is not changed or if it's invalid (undefined)
        if (answer.initialValue === answer[question.value] || _.isUndefined(answer[question.value]) && !_.isUndefined(answer.initialValue)) {
            answer[question.value] = answer.initialValue;
            deferred.resolve();
            return deferred.promise;
        }

        // avoid to save questions when its a new questionnaire / survey
        // the save is provided by generate button for all new questionnaires, surveys, questions and answers.
        if (_.isUndefined(questionnaire.id)) {
            deferred.resolve();
            return deferred.promise;
        }

        Restangular.restangularizeElement(null, answer, 'answer');
        Restangular.restangularizeElement(null, question, 'question');

        // delete answer if no value
        if (answer.id && !$scope.isValidNumber(answer[question.value])) {
            $scope.removeAnswer(question, answer);
            deferred.resolve();

            // update
        } else if (answer.id) {

            if (answer.permissions) {
                updateAnswer(answer, questionnaire).then(function() {
                    answer.initialValue = answer[question.value];
                    deferred.resolve(answer);
                });
            } else {
                $scope.getPermissions(question, answer).then(function() {
                    updateAnswer(answer, questionnaire).then(function() {
                        answer.initialValue = answer[question.value];
                        deferred.resolve(answer);
                    });
                });
            }

            // create answer, if allowed by questionnaire
        } else if (_.isUndefined(answer.id) && $scope.isValidNumber(answer[question.value]) && $scope.questionnairesStatus[questionnaire.status]) {
            answer.questionnaire = questionnaire.id;
            question.survey = questionnaire.survey.id;

            // if question is not created, create it before creating the answer
            getOrSaveQuestion(question).then(function(question) {
                createAnswer(answer, question, {part: part}).then(function() {
                    answer.initialValue = answer[question.value];
                    deferred.resolve(answer);
                    $scope.refresh(false, true);
                });
            });
        }

        return deferred.promise;
    };

    /**
     * Save one questionnaire if specified or all if it's not.
     * @param questionnaire
     */
    $scope.saveAll = function(questionnaire) {

        $scope.checkQuestionnairesIntegrity().then(function() {
            saveFilters().then(function(savedFacilities) {
                var questionnairesToCreate = !_.isUndefined(questionnaire) ? [questionnaire] : _.filter($scope.tabs.questionnaires, $scope.checkIfSavableQuestionnaire);
                var existingQuestionnaires = _.filter($scope.tabs.questionnaires, 'id');
                saveQuestionnaires(questionnairesToCreate.concat(existingQuestionnaires), savedFacilities);
            });
        });
    };

    /**
     * Check if a questionnaire is ready for save (has code, year, geoname and no errors
     * @param questionnaire
     * @returns {*}
     */
    $scope.checkIfSavableQuestionnaire = function(questionnaire) {
        if (_.isUndefined(questionnaire.id) && !_.isUndefined(questionnaire.geoname) && !_.isUndefined(questionnaire.geoname.country) && !_.isUndefined(questionnaire.survey) && !_.isEmpty(questionnaire.survey.code) && !_.isUndefined(questionnaire.survey.year) && !hasErrors(questionnaire)) {
            return true;
        }
    };

    /**
     * Avoid new questionnaires to have the same country for a same survey and avoid a same survey code to have two different years
     */
    $scope.checkQuestionnairesIntegrity = function() {
        var deferred = $q.defer();

        // check for countries
        $timeout(function() {
            if (_.isEmpty($scope.tabs.questionnaires)) {
                deferred.resolve();
            } else {
                _.forEach($scope.tabs.questionnaires, function(q1, i) {
                    if (_.isUndefined(q1.id)) {
                        q1.errors = {
                            duplicateCountryCode: false,
                            codeAndYearDifferent: false,
                            countryAlreadyUsedForExistingSurvey: false
                        };

                        _.forEach($scope.tabs.questionnaires, function(q2, j) {
                            if (_.isUndefined(q2.id) && i != j) {
                                if (q1.geoname && q2.geoname && q1.geoname.country.id == q2.geoname.country.id) {
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
    };

    /**
     * Set the value of a input (ng-model) before the value is changed
     * Used in function saveAnswer().
     * Avoid to do some ajax requests when we just blur field without changing value.
     * @param question
     * @param answer
     */
    $scope.setAnswerInitialValue = function(question, answer, part) {
        answer.initialValue = answer[question.value];
        if (!answer.part && part) {
            answer.part = part;
        }
    };

    /**
     * Set the value of a input (ng-model) before the value is changed
     * Used in function savePopulation().
     * Avoid to do some ajax requests when we just blur field without changing value.
     * @param model
     * @param value
     */
    $scope.setInitialValue = function(model, value) {
        model.initialValue = value;
    };

    /**
     * Check if given value is positive number including 0, to avoid 0 to be interpreted as null in the template side
     * @param val
     * @returns {boolean}
     */
    $scope.isValidNumber = function(val) {
        if (_.isNumber(val) && val >= 0) {
            return true;
        }
        return false;
    };

    /**
     * Save question if it has a name
     * @param question
     * @param questionnaire
     */
    $scope.saveQuestion = function(question, questionnaire) {

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
    };

    /**
     * Recovers existing data from DB when and existing code/year/country are setted in empty questionnaire
     * @param questionnaire
     */
    $scope.completeQuestionnaire = function(questionnaire) {
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
                        $scope.firstQuestionnairesRetrieve = true;
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
    };

    /**
     * Remove question after retrieving permissions from server if not yet done
     * @param question
     * @param answer
     */
    $scope.removeAnswer = function(question, answer) {
        Restangular.restangularizeElement(null, answer, 'Answer');
        if (_.isUndefined(answer.permissions)) {
            $scope.getPermissions(question, answer).then(function() {
                deleteAnswer(question, answer);
            });
        } else {
            deleteAnswer(question, answer);
        }
    };

    /**
     * Add column (questionnaire)
     */
    $scope.addQuestionnaire = function() {
        if (_.isUndefined($scope.tabs.questionnaires)) {
            $scope.tabs.questionnaires = [];
        }

        var questionnaire = {};
        if ($location.search().usedCountry) {
            questionnaire = {
                geoname: {
                    country: $location.search().usedCountry
                }
            };
        }

        $scope.tabs.questionnaires.splice(0, 0, questionnaire);
        $scope.questionnairesAreSorted = false;
        fillMissingElements();
        updateUrl('questionnaires');
    };

    /**
     * Remove column (questionnaire)
     * @param index
     */
    $scope.removeQuestionnaire = function(index) {
        $scope.tabs.questionnaires.splice(index, 1);
        updateUrl('questionnaires');
    };

    /**
     * Remove row (filter)
     * @param filter
     */
    $scope.removeFilter = function(filter) {

        var filters = _.uniq(_.pluck([filter
        ].concat(getChildrenRecursively(filter)), 'id'));

        // remove filter if it's in list of
        $scope.tabs.filters = _.filter($scope.tabs.filters, function(f) {
            if (!_.contains(filters, f.id)) {
                return true;
            }
            return false;
        });

        updateUrl('filters');

        _.forEach(filters, function(f) {
            removeQuestionsForFilter(f.id, false);
        });
    };

    /**
     * Add a filter to list
     * Filters that have been added must have and ID because the questions are indexed on their filter id,
     * This function assigns an arbitrary ID starting with and underscore that is replaced on save.
     * This underscored id is used for children filters that need a reference to parents.
     */
    $scope.addEquipment = function() {
        if ($scope.tabs.filter) {
            if (_.isUndefined($scope.tabs.filters)) {
                $scope.tabs.filters = [];
            }
            $scope.tabs.filters.push({
                id: "_" + $scope.lastFilterId++,
                level: 1,
                parents: [
                    {id: $scope.tabs.filter.id}
                ]
            });
            fillMissingElements();
        }
    };

    /**
     * Create a filterset with a filter using the same name. Is used on sector mode.
     */
    $scope.createFilterSet = function() {
        Restangular.all('filterSet').post($scope.tabs.newFilterSet).then(function(filterSet) {
            var filter = {
                name: $scope.tabs.newFilterSet.name,
                filterSets: [filterSet.id],
                color: '#FF0000'
            };
            saveFilter(filter).then(function(filter) {
                $scope.tabs.filter = filter;
                $scope.tabs.view = true;
                $scope.tabs.viewDisabled = false;
                $scope.tabs.create = false;
                $scope.tabs.createDisabled = true;

                // Automatically insert empty questionnaire and equipement
                $scope.addQuestionnaire();
                $timeout($scope.addEquipment, 800); // TODO, this is absolutely terrible, I think I need timeout because the new sector filter did not load its children yet, but I am not sure. Samuel, help !
            });
        });
    };

    $scope.saveComment = function(questionnaire) {
        Restangular.restangularizeElement(null, questionnaire, 'Questionnaire');
        questionnaire.put();
    };

    /**
     * Detects if a ID is temporary (with underscore) or not. Used to detect unsaved filters.
     * Return true if filter has a numeric ID without underscore or false if not.
     * @param filter
     * @returns {boolean}
     */
    $scope.isValidId = function(filter) {
        if (_.isUndefined(filter.id) || /_\d+/.test(filter.id)) {
            return false;
        } else {
            return true;
        }
    };

    $scope.copyFilterUsages = function(dest, src) {

        if (dest.id && src.id) {
            // add an array with 1 element to disable the ability to duplicate formulas again
            dest.filterQuestionnaireUsages = true;
            dest.isLoading = true;
            $http.get('/api/questionnaire/copyFilterUsages', {
                params: {
                    dest: dest.id,
                    src: src.id
                }
            }).success(function() {
                dest.isLoading = false;
                $scope.refresh(false, true);
            });
        }
    };

    $scope.toggleQuestionAbsolute = function(questionnaire, question) {

        var isAbsolute = !question.isAbsolute;
        var questionnaires = getSurveysWithSameCode(questionnaire.survey.code);

        _.forEach(questionnaires, function(questionnaire) {
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
                    $scope.refresh(false, true);
                });

            }, 0);

        });
    };

    $scope.getFiltersByLevel = function(level, filters) {
        filters = _.isUndefined(filters) ? $scope.tabs.filters : filters;
        return _.filter(filters, function(filter) {
            if (filter.level == level) {
                return true;
            }
            return false;
        });
    };

    $scope.setExpandHierarchy = function(bool) {
        $scope.expandHierarchy = bool;
    };

    /**************************************************************************/
    /****************************************************** Private functions */
    /**************************************************************************/

    /**
     * Save given questionnaires and create sector rules for given filters (rules only if sector mode)
     * This method regroup questionnaires by survey, then save first questionnaire of each survey and his questions
     * Then, when questions have been saved, they're propagated to other questionnaires from same survey in client side
     * Then, other questionnaires are saved, without questions
     * Answers are always saved if needed
     * @param questionnairesToSave
     * @param savedFacilities
     */
    var saveQuestionnaires = function(questionnairesToSave, savedFacilities) {

        var questionnairesToSaveBySurvey = _.groupBy(questionnairesToSave, function(q) {
            return q.survey.code;
        });

        _.forEach(questionnairesToSaveBySurvey, function(questionnaires) {
            // save the first questionnaire (and his questions)
            var questionnaire = questionnaires.shift();
            if (!questionnaire.id) {
                savedFacilities = $scope.getFiltersByLevel(1);
            }
            saveCompleteQuestionnaire(questionnaire).then(function(questionnaire) {
                createQuestionnaireFilterUsages(questionnaire, savedFacilities);
            }, function() {
            }, function() {
                // notification when questions have been safed
                // then, once the questions have been created, save all other questionnaires
                _.forEach(questionnaires, function(questionnaire) {
                    if (!questionnaire.id) {
                        savedFacilities = $scope.getFiltersByLevel(1);
                    }
                    saveCompleteQuestionnaire(questionnaire).then(function(questionnaire) {
                        createQuestionnaireFilterUsages(questionnaire, savedFacilities);
                    });
                });
            });
        });
    };

    /**
     * Check if in NSA view, we have a single root filter, then x equipments and 2 children by equipment
     */
    var validateNSAFilterStructure = function() {
        $scope.NSAStructureOk = true;
        if ($scope.tabs.filters && $scope.tabs.filters[0]) {
            var rootLevel = $scope.tabs.filters[0].level;
            var rootFilters = $scope.getFiltersByLevel(rootLevel);
            if (rootFilters.length == 1) {
                var filtersLvl1 = $scope.getFiltersByLevel(rootLevel + 1);

                _.forEach(filtersLvl1, function(filter) {
                    var children = getChildren(filter);
                    if (children.length != 2) {
                        $scope.NSAStructureOk = false;
                        return false;
                    }
                });
            } else {
                $scope.NSAStructureOk = false;
            }
        }
    };

    /**
     * Get all children and children's children, by searching in parents references
     * @param filter
     * @returns {Array}
     */
    var getChildrenRecursively = function(filter) {
        var children = _.filter($scope.tabs.filters, function(f) {
            if (_.find(f.parents, {id: filter.id})) {
                return true;
            }
        });

        _.forEach(children, function(f) {
            children = children.concat(getChildrenRecursively(f));
        });

        return children;
    };

    /**
     * Get immediate children, by searching in parents references
     * @param filter
     * @returns {Array}
     */
    var getChildren = function(filter) {
        var children = _.filter($scope.tabs.filters, function(f) {
            if (_.find(f.parents, {id: filter.id})) {
                return true;
            }
        });

        return children;
    };

    /**
     * Call questionnaires asking for passed fields and executing callback function passing received questionnaires
     * @param questionnaires
     * @param fields
     * @param callback
     */
    var getQuestionnaires = function(questionnaires, fields) {
        var deferred = new $q.defer();

        if (questionnaires.length === 1 && !_.isUndefined(questionnaires[0])) {
            Restangular.one('questionnaire', questionnaires[0]).get(fields).then(function(questionnaire) {
                deferred.resolve([questionnaire]);
            });
        } else if (questionnaires.length > 1) {
            Restangular.all('questionnaire').getList(_.merge({id: questionnaires.join(',')}, fields)).then(function(questionnaires) {

                // when retrieve questionnaire with read permissions, remove pré-selected questionnaires from list if they're not received
                var removedQuestionnaires = _.difference(_.pluck($scope.tabs.questionnaires, 'id'), _.pluck(questionnaires, 'id'));
                _.forEach(removedQuestionnaires, function(questionnaireId) {
                    var index = _.findIndex($scope.tabs.questionnaires, {id: questionnaireId});
                    if (index >= 0) {
                        $scope.tabs.questionnaires.splice(index, 1);
                    }
                });

                deferred.resolve(questionnaires);
            }, function() {
                $scope.tabs.questionnaires = [];
            });
        }

        return deferred.promise;
    };

    /**
     * Check glass questionnaires and add them to a specific array that add a tab
     * If there is only one Glass questionnaire and no JMP, a redirection display the questionnaire
     * @param questionnaires
     */
    var checkGlassQuestionnaires = function(questionnaires) {
        var glass = [];
        var jmp = [];
        angular.forEach(questionnaires, function(questionnaire) {
            if (_.find(questionnaire.survey.questions, function(question) {
                return question.type != 'Numeric';
            })) {
                glass.push(questionnaire);
            } else {
                jmp.push(questionnaire);
            }
        });

        // if there is only 1 glass in all selected questionnaires, consider that user want to edit this one, and redirect to glass template
        // no action is possible on multiple glass questionnaires (neither browse or contribute)
        if (glass.length === 1 && $scope.tabs.questionnaires.length === 1) {
            $location.url('/contribute/glaas/' + glass[0].id + "?returnUrl=" + $location.path());

            // else list glass questionnaires apart
        } else {
            $scope.tabs.glass = glass;

            // remove glass questionnaires from selected questionnaires
            $scope.tabs.questionnaires = _.filter($scope.tabs.questionnaires, function(q) {
                if (!_.find(glass, {id: q.id})) {
                    return true;
                }
                return false;
            });

            // get data for new jmp questionnaires
            getQuestionnaires(_.pluck(jmp, 'id'), questionnaireWithAnswersFields).then(function(questionnaires) {
                $scope.firstQuestionnairesRetrieve = true;
//                listQuestionnairesWithFilterUsages(questionnaires);
                prepareDataQuestionnaires(questionnaires);
            });
        }
    };

    /**
     * Index answers and populations by part and questions by filters on questionnaire that have data from DB
     * @param questionnaires
     */
    var prepareDataQuestionnaires = function(questionnaires) {
        angular.forEach(questionnaires, function(questionnaire) {

            _.forEach(questionnaire.survey.questions, function(question) {
                if (question.answers) {
                    // index answers by part id
                    var answers = {};
                    _.forEach(question.answers, function(answer) {
                        if (answer.questionnaire && answer.questionnaire.id == questionnaire.id) {
                            answer.initialValue = question.isAbsolute ? answer.valueAbsolute : answer.valuePercent;
                            answers[answer.part.id] = answer;
                        }
                    });
                    question.answers = answers;
                }
            });

            // Index population by part id
            var restPopulations = _.map(questionnaire.populations, function(population) {
                return Restangular.restangularizeElement(null, population, 'population');
            });
            questionnaire.populations = _.indexBy(restPopulations, function(p) {
                return p.part.id;
            });

            _.forEach(questionnaire.survey.questions, function(q) {
                $scope.initQuestionAbsolute(q);
            });

            questionnaire.survey.questions = _.indexBy(questionnaire.survey.questions, function(q) {
                return q.filter.id;
            });

            // Indexes usages by filter and part
            var usagesByFilter = {};
            _.forEach(questionnaire.filterQuestionnaireUsages, function(usage) {

                if (!usagesByFilter[usage.filter.id]) {
                    usagesByFilter[usage.filter.id] = {};
                }

                if (!usagesByFilter[usage.filter.id][usage.part.id]) {
                    usagesByFilter[usage.filter.id][usage.part.id] = [];
                }
                usagesByFilter[usage.filter.id][usage.part.id].push(usage);
            });
            questionnaire.filterQuestionnaireUsagesByFilterAndPart = usagesByFilter;

            // update $scope with modified questionnaire
            $scope.tabs.questionnaires[_.findIndex($scope.tabs.questionnaires, {id: questionnaire.id})] = questionnaire;
        });

        fillMissingElements();
        getComputedFilters();
    };

    /**
     * Returns wether the special Exclude rule exists in the given usage
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
            questionnaire.filterQuestionnaireUsagesByFilterAndPart[filterId][partId] = [];
        }

        var usages = questionnaire.filterQuestionnaireUsagesByFilterAndPart[filterId][partId];
        if ($scope.excludeRuleExists(usages)) {
            _.forEach(usages, function(usage) {
                if (usage.rule.id == excludeRuleId) {
                    Restangular.restangularizeElement(null, usage, 'filterQuestionnaireUsage');
                    usage.remove().then(function() {
                        questionnaire.filterQuestionnaireUsagesByFilterAndPart[filterId][partId] = _.without(usages, usage);
                        $scope.refresh(false, true);
                    });
                }
            });
        } else {
            var usage = {
                isSecondLevel: true,
                filter: filterId,
                questionnaire: questionnaire.id,
                part: partId,
                rule: excludeRuleId,
                justification: '', // should have something meaningful
                sorting: -1 // guarantee that the rule overrides all other existing rules
            };

            Restangular.all('filterQuestionnaireUsage').post(usage).then(function(newUsage) {
                usages.push(newUsage);
                $scope.refresh(false, true);
            });
        }
    };

    /**
     * Called when a new questionnaire is added or filters are changed.
     * Ensure there is empty objects to grant app to work fine (e.g emptyanswers have to exist before ng-model assigns a value)
     */
    var fillMissingElements = function() {
        if ($scope.tabs.questionnaires && $scope.tabs.filters) {

            _.forEach($scope.tabs.questionnaires, function(questionnaire) {

                if (_.isUndefined(questionnaire.survey)) {
                    questionnaire.survey = {};
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

                _.forEach($scope.parts, function(part) {
                    if (_.isUndefined(questionnaire.populations[part.id])) {
                        var emptyPopulation = {
                            part: part.id
                        };
                        Restangular.restangularizeElement(null, emptyPopulation, 'population');
                        questionnaire.populations[part.id] = emptyPopulation;
                    }
                });

                _.forEach($scope.tabs.filters, function(filter) {
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

                    $scope.initQuestionAbsolute(questionnaire.survey.questions[filter.id]);

                    if (_.isUndefined(questionnaire.survey.questions[filter.id].answers)) {
                        questionnaire.survey.questions[filter.id].answers = {};
                    }

                    _.forEach($scope.parts, function(part) {
                        questionnaire.survey.questions[filter.id].answers[part.id] = getEmptyAnswer(questionnaire.survey.questions[filter.id].answers[part.id], questionnaire.id, questionnaire.survey.questions[filter.id].id, part.id);
                    });
                });

            });
        }
    };

    /**
     * Update questionnaires permissions
     * @type Function
     * @param questionnaires
     */
    var updateQuestionnairePermissions = function(questionnaires) {
        _.forEach($scope.tabs.questionnaires, function(questionnaire) {
            questionnaire.permissions = _.find(questionnaires, {id: questionnaire.id}).permissions;
        });
    };

    /**
     * Init computed filters
     * @type {null}
     */
    var getComputedFiltersCanceller = null;
    var getComputedFilters = function() {
        if (!$scope.firstQuestionnairesRetrieve || $scope.sector) {
            return;
        }
        $timeout(function() {

            var filtersIds = _.pluck($scope.tabs.filters, 'id');
            var questionnairesIds = _.compact(_.pluck($scope.tabs.questionnaires, 'id')); // compact remove falsey values

            if (filtersIds.length > 0 && questionnairesIds.length > 0) {
                $scope.isComputing = true;

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
                    _.forEach($scope.tabs.questionnaires, function(scopeQuestionnaire) {
                        if (questionnaires[scopeQuestionnaire.id]) {
                            _.forEach(questionnaires[scopeQuestionnaire.id], function(values, filterId) {
                                scopeQuestionnaire.survey.questions[filterId].filter.values = values;
                            });
                        }
                    });
                    $scope.isComputing = false;
                });

                // Also get the questionnaireusage for all questionnaires
                $http.get('/api/questionnaireUsage/compute', {
                    timeout: getComputedFiltersCanceller.promise,
                    params: {
                        questionnaires: questionnairesIds.join(',')
                    }
                }).success(function(questionnaireUsages) {
                    $scope.questionnaireUsages = questionnaireUsages;
                });
            }
        }, 0);
    };

    /**
     * Create a questionnaire, recovering or creating related survey and questions
     * @param questionnaire
     */
    var saveCompleteQuestionnaire = function(questionnaire) {
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
                    $scope.savePopulation(questionnaire, population);
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
                                answersPromises.push($scope.saveAnswer(answer, question, undefined, questionnaire));
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
    };

    /**
     * When recovering data from BD, propagates this data on survey that have the same code.
     * @param survey
     */
    var propagateSurvey = function(survey) {
        _.forEach(getSurveysWithSameCode(survey.code), function(questionnaire) {
            questionnaire.survey.id = survey.id;
            propagateQuestions(survey, true, true);
        });
    };

    /**
     * Multiple questionnaires may have the same survey, but the questions are linked to questionnaires with the right answers.
     * So questions may be in multiple questionnaires but they have to be synced for labels and id. A filter is supposed to have only one question.
     * This function propagates modifications on other questionnaires that have the same code.
     * @param survey the source of the question
     * @param {boolean} propagateId
     * @param {boolean} propagateName
     */
    var propagateQuestions = function(survey, propagateId, propagateName) {

        if (survey.code && survey.questions) {
            var questionnairesWithSameLabel = getSurveysWithSameCode(survey.code);

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
    };

    var getSurveysWithSameCode = function(code) {
        if (!_.isUndefined(code)) {
            var c2 = _.isNumber(code) ? code : code.toUpperCase();
            var questionnaires = _.filter($scope.tabs.questionnaires, function(q) {
                if (!_.isUndefined(q.survey.code)) {
                    var c1 = _.isNumber(q.survey.code) ? q.survey.code : q.survey.code.toUpperCase();
                    if (c1 == c2) {
                        return true;
                    }
                }
            });

            return questionnaires;
        } else {
            return [];
        }
    };

    /**
     * Create a questionnaire object in database.
     * @param questionnaire
     * @returns promise
     */
    var getOrSaveUnitQuestionnaire = function(questionnaire) {
        var deferred = $q.defer();

        if (_.isUndefined(questionnaire.id)) {

            // create a mini questionnaire object, to avoid big amounts of data to be sent to server
            var miniQuestionnaire = {
                dateObservationStart: questionnaire.survey.year + '-01-01',
                dateObservationEnd: questionnaire.survey.year + '-12-31',
                geoname: questionnaire.geoname.country.geoname.id,
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
    };

    /**
     * Save valid new population data for the given questionnaire
     * @param questionnaire
     * @param population
     * @returns void
     */
    $scope.savePopulation = function(questionnaire, population) {

        // Do nothing if the value did not change (avoid useless ajax)
        if (population.initialValue === population.population || !questionnaire.survey || !questionnaire.id || !questionnaire.geoname) {
            return;
        }
        $scope.setInitialValue(population, population.population);

        // Be sure that population data are in sync with other data from questionnaire/survey
        population.questionnaire = questionnaire.id;
        population.year = questionnaire.survey.year;
        population.country = questionnaire.geoname.country.id;

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

        $scope.refresh(false, true);
    };

    /**
     * Get survey, or create it if dont exist
     * @param questionnaire
     */
    var getOrSaveSurvey = function(questionnaire) {
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
    };

    /**
     * This function recovers surveys by searching with Q params
     * If there is similar code, search if the country is already used
     * @param questionnaire
     * @returns null|survey Return null if no survey exists, returns the survey if exist or reject promise if country already used
     */
    var getSurvey = function(questionnaire) {
        var deferred = $q.defer();

        if (_.isUndefined(questionnaire.survey.id) && !_.isEmpty(questionnaire.survey.code)) {

            Restangular.all('survey').getList({q: questionnaire.survey.code, perPage: 1000, fields: 'questions,questions.filter,questionnaires,questionnaires.geoname,questionnaires.geoname.country'}).then(function(surveys) {
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
                                    if (questionnaire.geoname.country.id == q.geoname.country.id) {
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
    };

    /**
     * Get and empty answer ready to be used as model and with right attributs setted
     */
    var getEmptyAnswer = function(answer, questionnaire, question, part) {
        answer = answer ? answer : {};

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
    };

    /**
     * Update answers considering answer permissions
     * @param answer
     * @param questionnaire
     */
    var updateAnswer = function(answer, questionnaire) {
        var deferred = $q.defer();
        if (answer.id && (answer.permissions && answer.permissions.update || !answer.permissions && questionnaire && $scope.questionnairesStatus[questionnaire.status])) {
            Restangular.restangularizeElement(null, answer, 'Answer');

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
                $scope.refresh(false, true);
            }, function(data) {
                answer.error = data;
                deferred.reject();
            });
        } else {
            deferred.resolve();
        }

        return deferred.promise;
    };

    /**
     * Update question considering questionnaire permissions
     * @param questionnaire
     * @param question
     */
    var updateQuestion = function(questionnaire, question) {
        var deferred = $q.defer();
        if (question.id && $scope.questionnairesStatus[questionnaire.status]) {
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

    /**
     * Delete answer considering answer permissions
     * @param question
     * @param answer
     */
    var deleteAnswer = function(question, answer) {

        if (answer.id && answer.permissions.delete) {
            answer.isLoading = true;
            answer.remove().then(function() {
                delete(answer.id);
                delete(answer[question.value]);
                delete(answer.edit);
                answer.isLoading = false;
                $scope.refresh(false, true);
            });
        }
    };

    /**
     * Create answer considering *questionnaire* permissions
     * @param answer
     * @param question
     * @param params
     */
    var createAnswer = function(answer, question, params) {
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
    };

    /**
     * use question or save if necessary and return result
     * @param question
     */
    var getOrSaveQuestion = function(question) {
        var deferred = $q.defer();

        if (!$scope.isValidId(question.filter)) {
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
    };

    /**
     * In case sector parameter is detected in url, this function ensures each filter has
     * sub filters dedicated to filter data (Usually people and equipment but may be anything)
     */
    var prepareSectorFilters = function() {
        if ($scope.tabs.filters && $scope.mode.isSector) {

            var equipments = _.filter($scope.tabs.filters, function(f) {
                if (!$scope.isValidId(f) && f.level == 1) {
                    return true;
                }
                return false;
            });
            var sectorChildrenNames = $scope.sectorChildren.split(',');

            _.forEachRight(equipments, function(equipment) {
                // get equipment children filter
                var equipementData = _.filter($scope.tabs.filters, function(f) {
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
                    var index = _.findIndex($scope.tabs.filters, {id: equipment.id});
                    _.forEach(sectorChildFilters, function(filter, sectorIndex) {
                        $scope.tabs.filters.splice(index + sectorIndex + 1, 0, filter);
                    });
                }
            });
            fillMissingElements();
        }
    };

    /**
     * When removing a filter, this function remove related questions on questionnaires to ensure no unwanted operation on DB is made
     * @param newFilters
     * @param oldFilters
     */
    var removeUnUsedQuestions = function(newFilters, oldFilters) {
        var removedFilters = _.difference(_.pluck(oldFilters, 'id'), _.pluck(newFilters, 'id'));
        _.forEach(removedFilters, function(filterId) {
            removeQuestionsForFilter(filterId, false);
        });
    };

    var removeQuestionsForFilter = function(id, isRegex) {
        _.forEach($scope.tabs.questionnaires, function(questionnaire) {
            // only remove questions on new questionnaires, others have received data from DB and shouldn't be removed
            if (_.isUndefined(questionnaire.id)) {
                _.forEach(questionnaire.survey.questions, function(question, filterId) {
                    if (isRegex && id.test(filterId) || !isRegex && id == filterId) {
                        delete(questionnaire.survey.questions[filterId]);
                    }
                });
            }
        });
    };

    /**
     * Save all filters, equipment before their children
     * @returns promise
     */
    var saveFilters = function() {
        var deferred = $q.defer();

        // get all filters with starting by _1
        var equipments = _.filter($scope.tabs.filters, function(f) {
            if (/^_\d+/.test(f.id)) {
                return true;
            }
        });

        if (_.isEmpty(equipments)) {
            deferred.resolve();
        } else {
            saveFiltersCollection(equipments).then(function() {

                // get all filters with starting by __1
                var nbPersonsFilters = _.filter($scope.tabs.filters, function(f) {
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
    };

    /**
     * Save given collection of filters
     * @param filtersToSave
     * @returns promise
     */
    var saveFiltersCollection = function(filtersToSave) {
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
    };

    /**
     * Save a single filter
     * @param filter
     * @returns promise
     */
    var saveFilter = function(filter) {
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
    };

    /**
     * When saving a filter with temporary url, we need to update question filter and index with new filter id.
     * @param filter
     */
    var replaceQuestionsIds = function(filter) {
        _.forEach($scope.tabs.questionnaires, function(questionnaire) {
            if (questionnaire.survey && questionnaire.survey.questions && questionnaire.survey.questions[filter.oldId]) {
                questionnaire.survey.questions[filter.id] = questionnaire.survey.questions[filter.oldId];
                questionnaire.survey.questions[filter.id].filter.id = filter.id;
                delete(questionnaire.survey.questions[filter.oldId]);
            }
        });
    };

    /**
     * Remplace id related to filters that have temporary id by the new ID returned by DB.
     * @param filter
     */
    var replaceIdReferenceOnChildFilters = function(filter) {
        _.forEach($scope.tabs.filters, function(f) {
            _.forEach(f.parents, function(parent) {
                if (parent.id == filter.oldId) {
                    parent.id = filter.id;
                }
            });
        });
    };

    /**
     * Create usages only if we are in sector mode
     * As we cancel sector mode at
     * @param questionnaire
     * @param filters
     */
    var createQuestionnaireFilterUsages = function(questionnaire, filters) {
        if ($scope.mode.isSector) {
            var equipments = _.map($scope.getFiltersByLevel(1, filters), function(filter) {
                return filter.id + ':' + _.pluck(getChildren(filter), 'id').join('-');
            });

            _.forEach($scope.tabs.questionnaires, function(questionnaire) {
                questionnaire.filterQuestionnaireUsages = 1;
            });

//            listQuestionnairesWithFilterUsages($scope.tabs.questionnaires);

            $http.get('/api/filter/createUsages', {
                params: {
                    filters: equipments.join(','),
                    questionnaires: questionnaire.id
                }
            }).success(function() {
                $scope.firstQuestionnairesRetrieve = true;
                $scope.refresh(false, true);
            });
        }
    };

    /**
     * Questionnaires with usages
     * @param questionnaires
     */
//    Disabled for the moment, because add buttons that are not required since the news NSA view has been added, but works
//    var listQuestionnairesWithFilterUsages = function(questionnaires) {
//        $scope.questionnairesWithUsages = _.filter(questionnaires, function(q) {
//            if (!_.isEmpty(q.filterQuestionnaireUsages) || _.isNumber(q.filterQuestionnaireUsages)) {
//                return true;
//            }
//        });
//    };

    /**
     * Update parameters on url exlucding empty ids to avoid multiple consecutive commas that cause problems on server side.
     * @param element
     */
    var updateUrl = function(element) {
        $location.search(element, _.filter(_.pluck($scope.tabs[element], 'id'), function(el) {
            if (el) {
                return true;
            }
        }).join(','));
    };

    /**
     * Hide selection panels on :
     *  - survey selection
     *  - country selection
     *  - filter set selection
     *  - filter's children selection
     *  - page loading
     *
     *  If there are filter and questionnaires selected after this manipulation
     *  Don't hide selection panes if select with free selection tool on "Selected" tab.
     *  The button "Expand/Compress Selection" reflects this status and allow to change is again.
     */
    var checkSelectionExpand = function() {
        firstLoading = false;
        if ($scope.tabs.filters && $scope.tabs.filters.length && $scope.tabs.questionnaires && $scope.tabs.questionnaires.length) {
            $scope.expandSelection = false;
        } else {
            $scope.expandSelection = true;
        }
    };

    /**
     * Check if questionnaire has one or multiple errors
     * @param questionnaire
     * @returns {boolean}
     */
    var hasErrors = function(questionnaire) {
        var hasErrors = false;
        _.forEach(questionnaire.errors, function(potentialError) {
            if (potentialError) {
                hasErrors = true;
                return false;
            }
        });
        return hasErrors;
    };

    /* Redirect functions */
    var redirect = function() {
        $location.url($location.search().returnUrl);
    };

    $scope.cancel = function() {
        redirect();
    };

    /**
     * Returns the type of cell, to be able to display correct icon
     * @param {questionnaire} questionnaire
     * @param {filter} filter
     * @param {integer} partId
     * @returns {String}
     */
    $scope.getCellType = function(questionnaire, filter, partId) {

        if (questionnaire.survey) {

            var question = questionnaire.survey.questions[filter.id];
            var answer;
            if (question) {
                answer = question.answers[partId];
            }

            if (question.isLoading || (answer && answer.isLoading)) {
                return 'loading';
            }

            var firstValue;
            if (question && question.filter.values && question.filter.values[partId]) {
                firstValue = question.filter.values[partId].first;
            }

            var usages;
            if (questionnaire.filterQuestionnaireUsagesByFilterAndPart && questionnaire.filterQuestionnaireUsagesByFilterAndPart[filter.id]) {
                usages = questionnaire.filterQuestionnaireUsagesByFilterAndPart[filter.id][partId];
            }

            if (answer && answer.error) {
                return 'error';
            } else if (answer && $scope.isValidNumber(answer[question.value])) {
                return 'answer';
            } else if (usages && usages.length) {
                return 'rule';
            } else if (filter.summands.length && $scope.isValidNumber(firstValue)) {
                return 'summand';
            } else if ($scope.isValidNumber(firstValue)) {
                return 'child';
            }
        }

        return 'nothing';
    };

    /**
     * Toggle the display of a formula, and load it if not already done
     * @param {rule} rule
     */
    $scope.toggleShowFormula = function(rule) {
        rule.show = !rule.show;
        if (!rule.structure) {
            Restangular.one('rule', rule.id).get({fields: 'structure'}).then(function(loadedRule) {
                rule.structure = loadedRule.structure;
            });
        }
    };
});
