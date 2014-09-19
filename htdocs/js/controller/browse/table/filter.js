angular.module('myApp').controller('Browse/FilterCtrl', function($scope, $location, $http, $timeout, Restangular, $q, $rootScope, requestNotification, $filter, Percent, Utility, questionnairesStatus, TableFilter) {
    'use strict';

    /**************************************************************************/
    /*********************************************** Variables initialisation */
    /**************************************************************************/

    // params for ajax requests
    $scope.filterFields = {fields: 'color,paths,parents,summands'};
    $scope.countryParams = {fields: 'geoname'};
    var questionnaireFields = {fields: 'survey.questions.type,survey.questions.filter'};
    var questionnaireWithAnswersFields = {fields: 'status,permissions,comments,geoname.country,survey.questions,survey.questions.isAbsolute,survey.questions.filter,survey.questions.alternateNames,survey.questions.answers.questionnaire,survey.questions.answers.part,populations.part,filterQuestionnaireUsages.isSecondStep,filterQuestionnaireUsages.sorting'};

    // Variables initialisations
    $scope.locationPath = $location.$$path;
    $scope.data = TableFilter.init($scope.locationPath);
    $scope.expandHierarchy = true;
    $scope.expandSelection = true;
    $scope.lastFilterId = 1;
    $scope.parts = Restangular.all('part').getList().$object;
    $scope.surveysTemplate = "[[item.code]] - [[item.name]]";
    $scope.questionnairesStatus = questionnairesStatus;

    // Expose function to scope
    $scope.isValidNumber = Utility.isValidNumber;
    $scope.getPermissions = TableFilter.getPermissions;
    $scope.refresh = TableFilter.refresh;
    $scope.toggleShowQuestionnaireUsages = TableFilter.toggleShowQuestionnaireUsages;

    /**************************************************************************/
    /***************************************** First execution initialisation */
    /**************************************************************************/

    $scope.questionnaireParams = {surveyType: $scope.data.mode.surveyType};
    $scope.surveyParams = {surveyType: $scope.data.mode.surveyType};

    // Ensure that we have a logged in user if we are going to contribute things
    if ($scope.data.mode.isContribute) {
        $rootScope.$broadcast('event:auth-loginRequired');
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

    var filterSetDeferred = $q.defer();
    if (!$location.search().filterSet) {
        filterSetDeferred.resolve();
    }

    var filterDeferred = $q.defer();
    if (!$location.search().filter) {
        filterDeferred.resolve();
    }
    $scope.$watch('data.filterSet', function() {
        if ($scope.data.filterSet) {
            $scope.data.filters = [];
            Restangular.one('filterSet', $scope.data.filterSet.id).getList('filters', _.merge($scope.filterFields, {perPage: 1000})).then(function(filters) {
                if (filters) {
                    $scope.data.filters = filters;
                    $scope.data.filter = null;
                    TableFilter.updateUrl('filter');
                    filterSetDeferred.resolve();
                }
                checkSelectionExpand();
            });
        }
    });

    $scope.$watch('data.filter', function() {
        if ($scope.data.filter) {

            if ($scope.isValidId($scope.data.filter)) {
                Restangular.one('filter', $scope.data.filter.id).getList('children', _.merge($scope.filterFields, {perPage: 1000})).then(function(filters) {
                    if (filters) {

                        // Inject parent as first filter, so we are able to see the "main" value
                        _.forEach(filters, function(filter) {
                            filter.level++;
                        });
                        var parent = _.clone($scope.data.filter);
                        parent.level = 0;
                        filters.unshift(parent);

                        $scope.data.filters = filters;
                        $scope.data.filterSet = null;
                        TableFilter.updateUrl('filterSet');

                        filterDeferred.resolve();
                    }
                    checkSelectionExpand();
                });
            } else {
                $scope.data.filters = [$scope.data.filter];
            }
        }
    });

    $scope.$watch('data.country', function() {
        if ($scope.data.country) {
            $scope.data.questionnaires = [];
            Restangular.one('geoname', $scope.data.country.geoname.id).getList('questionnaire', _.merge(questionnaireFields, {surveyType: $scope.data.mode.surveyType, perPage: 1000})).then(function(questionnaires) {
                $scope.data.questionnaires = questionnaires;
                $scope.data.survey = null;
                initSector();
                checkSelectionExpand();
            });
        }
    });

    $scope.$watch('data.survey', function() {
        if ($scope.data.survey) {
            $scope.data.questionnaires = [];
            Restangular.one('survey', $scope.data.survey.id).getList('questionnaire', _.merge(questionnaireFields, {surveyType: $scope.data.mode.surveyType, perPage: 1000})).then(function(questionnaires) {
                $scope.data.questionnaires = questionnaires;
                $scope.data.country = null;
                checkSelectionExpand();
            });
        }
    });

    var firstLoading = true;
    $q.all([filterSetDeferred, filterDeferred]).then(function() {
        $scope.$watch('data.filters', function(newFilters, oldFilters) {
            removeUnUsedQuestions(newFilters, oldFilters);
            fillMissingElements();
            TableFilter.getComputedFilters();
            if (firstLoading === true && $scope.data.filters && $scope.data.questionnaires) {
                checkSelectionExpand();
            }
        });
    });

    $scope.$watchCollection('data.filters', function() {
        prepareSectorFilters();
    });

    $scope.$watch('data.questionnaires', function(newQuests, oldQuests) {
        var newQuestionnaires = _.difference(_.pluck(newQuests, 'id'), _.pluck(oldQuests, 'id'));
        newQuestionnaires = newQuestionnaires ? newQuestionnaires : [];

        if (!_.isEmpty(newQuestionnaires)) {

            TableFilter.getQuestionnaires(newQuestionnaires, questionnaireWithAnswersFields).then(function(questionnaires) {
                $scope.firstQuestionnairesRetrieve = true;
                prepareDataQuestionnaires(questionnaires);
                $scope.orderQuestionnaires(false);
            });
        } else if (($scope.data.country || $scope.data.survey) && _.isEmpty($scope.data.questionnaires)) {
            $scope.addQuestionnaire();
        }

        if (firstLoading === true && $scope.data.filters && $scope.data.questionnaires) {
            checkSelectionExpand();
        }
    });

    /**************************************************************************/
    /******************************************************** Scope functions */
    /**************************************************************************/

    $scope.toggleOriginalDenominations = function() {
        var firstQuestionnaireStatus = !$scope.data.questionnaires[0].showLabels;
        _.forEach($scope.data.questionnaires, function(questionnaire) {
            questionnaire.showLabels = firstQuestionnaireStatus;
        });
    };

    $scope.orderQuestionnaires = function(reverse) {
        $scope.data.questionnaires = $filter('orderBy')($scope.data.questionnaires, 'survey.year', reverse);
        $scope.questionnairesAreSorted = true;
    };

    /**
     * Detect if there are empty questionnaires to display button "generate"
     * @returns {boolean}
     */
    $scope.isEmptyQuestionnaires = function() {
        var isEmptyQuestionnaires = false;
        _.forEachRight($scope.data.questionnaires, function(questionnaire) {
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
        var filters = _.filter($scope.data.filters, function(f) {
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
        if (_.isUndefined(question.isAbsolute) && !$scope.data.mode.isSector || question && question.isAbsolute === false) {
            question.isAbsolute = false;
            question.value = 'valuePercent';
            question.max = 100;

            // if absolute is undefined and we are in sector view -> set question as absolute by default
        } else if (_.isUndefined(question.isAbsolute) && $scope.data.mode.isSector || question && question.isAbsolute === true) {
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
            TableFilter.removeAnswer(question, answer);
            deferred.resolve();

            // update
        } else if (answer.id) {

            if (answer.permissions) {
                updateAnswer(answer, questionnaire).then(function() {
                    answer.initialValue = valueToBeSaved;
                    deferred.resolve(answer);
                });
            } else {
                TableFilter.getPermissions(question, answer).then(function() {
                    updateAnswer(answer, questionnaire).then(function() {
                        answer.initialValue = valueToBeSaved;
                        deferred.resolve(answer);
                    });
                });
            }

            // create answer, if allowed by questionnaire
        } else if (_.isUndefined(answer.id) && Utility.isValidNumber(valueToBeSaved) && $scope.questionnairesStatus[questionnaire.status]) {
            answer.questionnaire = questionnaire.id;
            question.survey = questionnaire.survey.id;

            // if question is not created, create it before creating the answer
            getOrSaveQuestion(question).then(function(question) {
                createAnswer(answer, question, {part: part}).then(function() {
                    answer.initialValue = valueToBeSaved;
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
                var questionnairesToCreate = !_.isUndefined(questionnaire) ? [questionnaire] : _.filter($scope.data.questionnaires, $scope.checkIfSavableQuestionnaire);
                var existingQuestionnaires = _.filter($scope.data.questionnaires, 'id');
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
            if (_.isEmpty($scope.data.questionnaires)) {
                deferred.resolve();
            } else {
                _.forEach($scope.data.questionnaires, function(q1, i) {
                    if (_.isUndefined(q1.id)) {
                        q1.errors = {
                            duplicateCountryCode: false,
                            codeAndYearDifferent: false,
                            countryAlreadyUsedForExistingSurvey: false
                        };

                        _.forEach($scope.data.questionnaires, function(q2, j) {
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
                    TableFilter.getQuestionnaires([data.questionnaire.id], questionnaireWithAnswersFields).then(function(questionnaires) {
                        $scope.firstQuestionnairesRetrieve = true;
                        prepareDataQuestionnaires(questionnaires);
                        TableFilter.updateUrl('questionnaires');
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
     * Add column (questionnaire)
     */
    $scope.addQuestionnaire = function() {
        if (_.isUndefined($scope.data.questionnaires)) {
            $scope.data.questionnaires = [];
        }

        var country = null;
        if ($location.search().usedCountry) {
            country = $location.search().usedCountry;
        } else if ($scope.data.country) {
            country = $scope.data.country.id;
        }

        var questionnaire = {
            geoname: {
                country: country
            }
        };

        $scope.data.questionnaires.splice(0, 0, questionnaire);
        $scope.questionnairesAreSorted = false;
        fillMissingElements();
        TableFilter.updateUrl('questionnaires');
    };

    /**
     * Remove row (filter)
     * @param filter
     */
    $scope.removeFilter = function(filter) {

        var filters = _.uniq(_.pluck([filter].concat(getChildrenRecursively(filter)), 'id'));

        // remove filter if it's in list of
        $scope.data.filters = _.filter($scope.data.filters, function(f) {
            if (!_.contains(filters, f.id)) {
                return true;
            }
            return false;
        });

        TableFilter.updateUrl('filters');

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
        if ($scope.data.filter) {
            if (_.isUndefined($scope.data.filters)) {
                $scope.data.filters = [];
            }
            $scope.data.filters.push({
                id: "_" + $scope.lastFilterId++,
                level: 1,
                parents: [
                    {id: $scope.data.filter.id}
                ]
            });
            fillMissingElements();
        }
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

    $scope.getFiltersByLevel = function(level, filters) {
        filters = _.isUndefined(filters) ? $scope.data.filters : filters;
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
     * Get all children and children's children, by searching in parents references
     * @param filter
     * @returns {Array}
     */
    var getChildrenRecursively = function(filter) {
        var children = _.filter($scope.data.filters, function(f) {
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
        var children = _.filter($scope.data.filters, function(f) {
            if (_.find(f.parents, {id: filter.id})) {
                return true;
            }
        });

        return children;
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

            _.forEach(questionnaire.survey.questions, function(q) {
                $scope.initQuestionAbsolute(q);
            });

            questionnaire.survey.questions = _.indexBy(questionnaire.survey.questions, function(q) {
                return q.filter.id;
            });

            TableFilter.indexFilterQuestionnaireUsages(questionnaire);

            // update $scope with modified questionnaire
            $scope.data.questionnaires[_.findIndex($scope.data.questionnaires, {id: questionnaire.id})] = questionnaire;
        });

        fillMissingElements();
        TableFilter.getComputedFilters();
    };

    /**
     * Called when a new questionnaire is added or filters are changed.
     * Ensure there is empty objects to grant app to work fine (e.g emptyanswers have to exist before ng-model assigns a value)
     */
    var fillMissingElements = function() {
        if ($scope.data.questionnaires && $scope.data.filters) {

            _.forEach($scope.data.questionnaires, function(questionnaire) {

                if (_.isUndefined(questionnaire.survey)) {
                    questionnaire.survey = {
                        type: $scope.data.mode.surveyType
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

                _.forEach($scope.parts, function(part) {
                    if (_.isUndefined(questionnaire.populations[part.id])) {
                        questionnaire.populations[part.id] = {
                            part: part.id
                        };
                    }
                });

                // Complete questions
                _.forEach($scope.data.filters, function(filter) {
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
                TableFilter.updateUrl('questionnaires');

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
        _.forEach(TableFilter.getSurveysWithSameCode($scope.data.questionnaires, survey.code), function(questionnaire) {
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
            var questionnairesWithSameLabel = TableFilter.getSurveysWithSameCode($scope.data.questionnaires, survey.code);

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
        Restangular.restangularizeElement(null, population, 'population');

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
     * Get an empty answer ready to be used as model and with right attributs set
     */
    var getEmptyAnswer = function(answer, questionnaire, question, part) {
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
    };

    /**
     * Update answers considering answer permissions
     * @param answer
     * @param questionnaire
     */
    var updateAnswer = function(answer, questionnaire) {
        var deferred = $q.defer();
        if (answer.id && (answer.permissions && answer.permissions.update || !answer.permissions && questionnaire && $scope.questionnairesStatus[questionnaire.status])) {
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

    var initSector = function() {
        if ($scope.data.mode.isSector) {
            TableFilter.updateUrl('questionnaires');
            $scope.data.filter = undefined;
            $scope.data.filters = [];
            TableFilter.updateUrl('filter');
            TableFilter.updateUrl('filters');

            if ($scope.data.country) {

                $http.get('/api/filter/getSectorFiltersForGeoname', {
                    params: {
                        geoname: $scope.data.country.geoname.id
                    }
                }).success(function(data) {
                    if (data.id) {
                        $scope.data.filter = data;
                    } else {
                        addSectorFilterSet();
                    }
                });
            }
        }
    };

    var addSectorFilterSet = function() {
        $scope.data.filter = {
            id: '_' + $scope.lastFilterId++,
            name: 'Sector : ' + $scope.data.country.name,
            level: 0,
            color: '#FF0000'
        };
        $timeout($scope.addEquipment, 0);
    };

    /**
     * In case sector parameter is detected in url, this function ensures each filter has
     * sub filters dedicated to filter data (Usually people and equipment but may be anything)
     */
    var prepareSectorFilters = function() {
        if ($scope.data.filters && $scope.data.mode.isSector) {

            var equipments = _.filter($scope.data.filters, function(f) {
                if (!$scope.isValidId(f) && f.level == 1) {
                    return true;
                }
                return false;
            });

            var sectorChildrenNames = ['Number of facilities', 'Persons per facilities'];

            _.forEachRight(equipments, function(equipment) {
                // get equipment children filter
                var equipementData = _.filter($scope.data.filters, function(f) {
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
                    var index = _.findIndex($scope.data.filters, {id: equipment.id});
                    _.forEach(sectorChildFilters, function(filter, sectorIndex) {
                        $scope.data.filters.splice(index + sectorIndex + 1, 0, filter);
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
        _.forEach($scope.data.questionnaires, function(questionnaire) {
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

        if (!$scope.isValidId($scope.data.filters[0])) {

            // first create filter set
            var newFilterSet = {name: 'Sector : ' + $scope.data.country.name};
            Restangular.all('filterSet').post(newFilterSet).then(function(filterSet) {
                $scope.data.filters[0].filterSets = [filterSet];

                // then save first filter
                saveFiltersCollection([$scope.data.filters[0]]).then(function() {
                    TableFilter.updateUrl('filter');
                    saveEquipments().then(function() {
                        deferred.resolve();
                    });
                });
            });

        } else {
            return saveEquipments();
        }

        return deferred.promise;
    };

    var saveEquipments = function() {
        var deferred = $q.defer();

        // get all filters with starting by _1
        var equipments = _.filter($scope.data.filters, function(f) {
            if (/^_\d+/.test(f.id)) {
                return true;
            }
        });

        if (_.isEmpty(equipments)) {
            deferred.resolve();
        } else {
            saveFiltersCollection(equipments).then(function() {

                // get all filters with starting by __1
                var nbPersonsFilters = _.filter($scope.data.filters, function(f) {
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
                TableFilter.updateUrl('filters');
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
        _.forEach($scope.data.questionnaires, function(questionnaire) {
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
        _.forEach($scope.data.filters, function(f) {
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
        if ($scope.data.mode.isSector) {

            var equipments = _.map($scope.getFiltersByLevel(1, filters), function(filter) {
                return filter.id + ':' + _.pluck(getChildren(filter), 'id').join('-');
            });

            _.forEach($scope.data.questionnaires, function(questionnaire) {
                questionnaire.filterQuestionnaireUsages = 1;
            });

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
        if ($scope.data.filters && $scope.data.filters.length && $scope.data.questionnaires && $scope.data.questionnaires.length) {
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
});
