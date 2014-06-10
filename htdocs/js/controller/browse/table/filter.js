angular.module('myApp').controller('Browse/FilterCtrl', function($scope, $location, $http, $timeout, Restangular, $q, $rootScope, requestNotification, $filter) {
    'use strict';

    /**************************************************************************/
    /***************************************** First execution initialisation */
    /**************************************************************************/

    if ($location.$$path.indexOf('/contribute') >= 0) {
        $scope.mode = 'Contribute';
    } else if ($location.$$path.indexOf('/browse') >= 0) {
        $scope.mode = 'Browse';
    }

    /**************************************************************************/
    /*********************************************** Variables initialisation */
    /**************************************************************************/

        // params for ajax requests
    $scope.filterParams = {fields: 'paths,color,genericColor', itemOnce: 'true'};
    $scope.filterSetFields = {fields: 'color,paths'};
    $scope.filterFields = {fields: 'color,paths'};
    $scope.countryParams = {fields: 'geoname'};
    var countryFields = {fields: 'geoname.questionnaires,geoname.questionnaires.survey,geoname.questionnaires.survey.questions,geoname.questionnaires.survey.questions.type,geoname.questionnaires.survey.questions.filter'};
    var questionnaireWithQTypeFields = {fields: 'survey.questions,survey.questions.type'};
    var questionnaireWithAnswersFields = {fields: 'filterQuestionnaireUsages,permissions,comments,geoname.country,survey.questions,survey.questions.isAbsolute,survey.questions.filter,survey.questions.alternateNames,survey.questions.answers.questionnaire,survey.questions.answers.part,populations.part'};
    var surveyFields = {fields: 'questionnaires.survey,questionnaires.survey.questions,questionnaires.survey.questions.type,questionnaires.survey.questions.filter'};

    // Subscribe to listen when there is network activity
    $scope.isLoading = false;
    requestNotification.subscribeOnRequest(function() {
        $scope.isLoading = true;
    }, function() {
        if (requestNotification.getRequestCount() === 0) {
            $scope.isLoading = false;
        }
    });

    // Variables initialisations
    $scope.expandSelection = true;
    $scope.lastFilterId = 1;
    $scope.firstQuestionnairesRetrieve = false; // avoid to compute filters before questionnaires have been retrieved, getComputedFilters() need ready base to complete data
    $scope.tabs = {};
    $scope.sectorChildren = 'Number of equipement,Persons per equipement';
    $scope.parts = Restangular.all('part').getList().$object;
    $scope.modes = ['Browse', 'Contribute'];
    $scope.surveysTemplate = "[[item.code]] - [[item.name]]";

    /**************************************************************************/
    /*************************************************************** Watchers */
    /**************************************************************************/

    $scope.$watch(function() {
        return $location.url();
    }, function() {
        if ($location.search().sectorChildren) {
            $scope.sector = true;
            if (!$scope.tabs.questionnaires) {
                $scope.addQuestionnaire();
            }
        } else {
            $scope.sector = false;
        }
        $scope.returnUrl = $location.search().returnUrl;
        $scope.currentUrl = encodeURIComponent($location.url());
    });

    $scope.$watch('sector', function() {
        if ($scope.sector) {
            $scope.max = 10000000000;
        } else {
            $scope.max = 1;
        }
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
            Restangular.one('filterSet', $scope.tabs.filterSet.id).getList('filters', _.merge($scope.filterSetFields, {perPage: 1000})).then(function(filters) {
                if (filters) {
                    $scope.tabs.filters = filters;
                    $scope.tabs.filter = null;
                }
                checkSelectionExpand();
            });
        }
    });

    $scope.$watch('tabs.filter', function() {
        if ($scope.tabs.filter) {
            Restangular.one('filter', $scope.tabs.filter.id).getList('children', _.merge($scope.filterFields, {perPage: 1000})).then(function(filters) {
                if (filters) {
                    $scope.tabs.filters = filters;
                    $scope.tabs.filterSet = null;
                    if ($scope.sector) {
                        $scope.addFilter();
                    }
                }
                checkSelectionExpand();
            });
        }
    });

    $scope.$watch('tabs.country', function() {
        if ($scope.tabs.country) {
            Restangular.one('country', $scope.tabs.country.id).get(_.merge(countryFields, {perPage: 1000})).then(function(country) {
                $scope.tabs.questionnaires = country.geoname.questionnaires;
                $scope.tabs.survey = null;
                checkSelectionExpand();
            });
        }
    });

    $scope.$watch('tabs.survey', function() {
        if ($scope.tabs.survey) {
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

    /**
     * Refreshing page means :
     *  - Recover all questionnaires permissions (in case user swith from browse to contribute/full view and need to be logged in)
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
     * @param callback
     */
    $scope.getPermissions = function(question, answer, callback) {

        if (answer.id && _.isUndefined(answer.permissions)) {
            Restangular.one('answer', answer.id).get({fields: 'permissions'}).then(function(newAnswer) {
                answer.permissions = newAnswer.permissions;

                // if value has been updated between permissions check, restore value
                if (!answer.permissions.update) {
                    answer[question.value] = newAnswer[question.value];
                }
                if (callback) {
                    callback(answer);
                }

            }, function() {
                answer.isLoading = false;
                answer.permissions = {
                    create: false,
                    read: false,
                    update: false,
                    delete: false
                };
            });
        }
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
     * Update an answer
     * Create an answer and if needed the question related
     * @param answer
     * @param question
     * @param filter
     * @param questionnaire
     * @param part
     */
    $scope.saveAnswer = function(answer, question, filter, questionnaire, part) {

        // complete question in all situations with filter name if there is no name specified
        if (_.isUndefined(question.name)) {
            question.name = filter.name;
        }

        // avoid to do some job if the value is not changed or if it's invalid (undefined)
        if (answer.initialValue === answer[question.value] || _.isUndefined(answer[question.value]) && !_.isUndefined(answer.initialValue)) {
            answer[question.value] = answer.initialValue;
            return;
        }

        // avoid to save questions when its a new questionnaire / survey
        // the save is provided by generate button for all new questionnaires, surveys, questions and answers.
        if (_.isUndefined(questionnaire.id)) {
            return;
        }

        Restangular.restangularizeElement(null, answer, 'answer');
        Restangular.restangularizeElement(null, question, 'question');

        // delete answer if no value
        if (answer.id && !$scope.toBoolNum(answer[question.value])) {
            $scope.removeAnswer(question, answer);

            // update
        } else if (answer.id) {

            if (answer.permissions) {
                updateAnswer(answer, question);
            } else {
                $scope.getPermissions(question, answer, updateAnswer);
            }

            // create answer, if allowed by questionnaire
        } else if (_.isUndefined(answer.id) && !_.isUndefined(answer[question.value]) && questionnaire.permissions.create) {
            answer.isLoading = true;
            answer.questionnaire = questionnaire.id;
            question.survey = questionnaire.survey.id;

            // if question is not created, create it before creating the answer
            getOrSaveQuestion(question).then(function(question) {
                answer.question = question.id;
                answer.part = part.id;
                createAnswer(question, answer).then(function() {
                    $scope.refresh(false, true);
                });

            });
        }
    };

    /**
     * Save one questionnaire if index is specified or all if it's not.
     * @param index
     */
    $scope.saveQuestionnaires = function(index) {

        var sector = $scope.sector;
        $scope.checkQuestionnairesIntegrity().then(function() {
            saveFilters().then(function() {
                if (_.isUndefined(index)) {
                    var questionnairesToSave = _.filter($scope.tabs.questionnaires, $scope.checkIfSavableQuestionnaire);
                    saveAllQuestionnairesWhenQuestionsAreSaved(questionnairesToSave, 0, sector);
                } else {
                    if ($scope.checkIfSavableQuestionnaire($scope.tabs.questionnaires[index])) {
                        saveCompleteQuestionnaire($scope.tabs.questionnaires[index]).then(function() {
                            createQuestionnaireFilterUsages(sector);
                        });
                    }
                }
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
            return questionnaire;
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
     * Select next view mode
     */
    $scope.nextMode = function() {
        _.forEach($scope.modes, function(mode, index) {
            if (mode === $scope.mode) {
                if (index === $scope.modes.length - 1) {
                    $scope.mode = $scope.modes[0];
                } else {
                    $scope.mode = $scope.modes[index + 1];
                }

                return false;
            }
        });
    };

    /**
     * Set the value of a input (ng-model) before the value is changed
     * Used in function saveAnswer().
     * Avoid to do some ajax requests when we just blur field without changing value.
     * @param question
     * @param answer
     */
    $scope.setAnswerInitialValue = function(question, answer) {
        if (answer[question.value]) {
            answer.initialValue = answer[question.value];
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
    $scope.toBoolNum = function(val) {
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
            });
        }
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
                    getQuestionnaires([data.questionnaire.id
                    ], questionnaireWithAnswersFields).then(function(questionnaires) {
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
            $scope.getPermissions(question, answer, deleteAnswer);
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
        $scope.tabs.questionnaires.splice(0, 0, {});
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
     * @param index
     */
    $scope.removeFilter = function(index) {

        var id = $scope.tabs.filters[index].id;
        $scope.tabs.filters.splice(index, 1);
        removeQuestionsForFilter(id, false);

        if ($scope.sector) {
            var temporaryFilterIdRegex = new RegExp('_' + id + "_");
            // remove temporary children sector filters
            $scope.tabs.filters = _.filter($scope.tabs.filters, function(filter) {
                if (!temporaryFilterIdRegex.test(filter.id)) {
                    return true;
                }
            });
            if (!_.isEmpty($scope.tabs.questionnaires)) {
                removeQuestionsForFilter(temporaryFilterIdRegex, true);
            }
        }
        updateUrl('filters');
    };

    /**
     * Add a filter to list
     * Filters that have been added must have and ID because the questions are indexed on their filter id,
     * This function assigns an arbitrary ID starting with and anderscore that is replaced on save.
     * This underscored id is used for children filters that need a reference to parents.
     */
    $scope.addFilter = function() {
        if ($scope.tabs.filter) {
            if (_.isUndefined($scope.tabs.filters)) {
                $scope.tabs.filters = [];
            }
            $scope.tabs.filters.push({
                id: "_" + $scope.lastFilterId++,
                level: 0,
                parents: [$scope.tabs.filter.id],
                sector: true
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
            });
        });
    };

    $scope.saveComment = function(questionnaire) {
        Restangular.restangularizeElement(null, questionnaire, 'Questionnaire');
        questionnaire.put();
    };

    /**
     * Detects if a ID is temporary (with underscore) or not. Used to detect unsaved filters.
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
            }).success(function()
            {
                dest.isLoading = false;
                $scope.refresh(false, true);
            });
        }
    };

    $scope.toggleQuestionAbsolute = function(questionnaire, question) {
        var bool = !question.isAbsolute;
        var questionnaires = getSurveysWithSameCode(questionnaire.survey.code);
        _.forEach(questionnaires, function(questionnaire) {
            question = questionnaire.survey.questions[question.filter.id];
            if (bool) {
                question.isAbsolute = true;
                question.value = 'valueAbsolute';
                question.max = 10000000000000000;
            } else {
                question.isAbsolute = false;
                question.value = 'valuePercent';
                question.max = 1;
            }
            updateQuestion(questionnaire, question);
            transfertValue(question, questionnaire, true);
        });
    };

    /**************************************************************************/
    /****************************************************** Private functions */
    /**************************************************************************/

    var transfertValue = function(question, questionnaire, updateAnswers) {
        _.forEach(question.answers, function(answer) {
            if (question.isAbsolute) {
                answer.valueAbsolute = answer.valuePercent;
                answer.valuePercent = null;
            } else {
                answer.valuePercent = answer.valueAbsolute;
                answer.valueAbsolute = null;
            }
            if (updateAnswers) {
                updateAnswer(answer, question, questionnaire);
            }
        });
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
                deferred.resolve(questionnaires);

            });
        }

        return deferred.promise;
    };

    /**
     * Check glass questiionnaires and add them to a specific array that add a tab
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
            $location.url('/contribute/questionnaire/glass/' + glass[0].id + "?returnUrl=" + $location.path());

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
                listQuestionnairesWithFilterUsages(questionnaires);
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
                        if (!_.isUndefined(answer.questionnaire) && answer.questionnaire.id == questionnaire.id) {
                            delete(answer.questionnaire);
                            answers[answer.part.id] = answer;
                        }
                    });
                    question.answers = answers;
                }
            });

            // Index population by part id
            var indexedPopulations = [];
            _.forEach(questionnaire.populations, function(population) {
                Restangular.restangularizeElement(null, population, 'population');
                indexedPopulations[population.part.id] = population;
            });
            questionnaire.populations = indexedPopulations;

            questionnaire.survey.questions = _.map(questionnaire.survey.questions, function(q) {
                if (q.isAbsolute) {
                    q.value = 'valueAbsolute';
                    q.max = 10000000000000000;
                } else {
                    q.value = 'valuePercent';
                    q.max = 1;
                }

                // Default alternate name to official one, so we can always display something
                if (_.isEmpty(q.alternateNames)) {
                    q.alternateNames = {};
                }
                if (!q.alternateNames[questionnaire.id]) {
                    q.alternateNames[questionnaire.id] = q.name;
                }

                return q;
            });

            questionnaire.survey.questions = _.indexBy(questionnaire.survey.questions, function(q) {
                return q.filter.id;
            });

            // update $scope with modified questionnaire
            $scope.tabs.questionnaires[_.findIndex($scope.tabs.questionnaires, {id: questionnaire.id})] = questionnaire;
        });

        fillMissingElements();
        getComputedFilters();
    };

    /**
     * Called when a new questionnaire is added or filters are changed.
     * Ensure there is empty objects to grand app to work fine (e.g emptyanswers have to exist before ng-model assigns a value)
     */
    var fillMissingElements = function() {
        if ($scope.tabs.questionnaires && $scope.tabs.filters) {

            _.forEach($scope.tabs.questionnaires, function(questionnaire) {

                if (_.isUndefined(questionnaire.survey)) {
                    questionnaire.survey = {};
                }

                if (_.isUndefined(questionnaire.permissions)) {
                    questionnaire.permissions = {
                        create: true,
                        update: true
                    };
                }

                if (_.isUndefined(questionnaire.survey.questions)) {
                    questionnaire.survey.questions = {};
                }

                // Create empty population indexed by the part ID
                if (_.isUndefined(questionnaire.populations)) {
                    questionnaire.populations = [];
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
                            type: 'Numeric'
                        };
                    }
                    if (filter.sectorChild) {
                        questionnaire.survey.questions[filter.id].isAbsolute = true;
                    } else if (_.isUndefined(questionnaire.survey.questions[filter.id].isAbsolute)) {
                        questionnaire.survey.questions[filter.id].isAbsolute = false;
                    }

                    if (questionnaire.survey.questions[filter.id].isAbsolute) {
                        questionnaire.survey.questions[filter.id].value = 'valueAbsolute';
                        questionnaire.survey.questions[filter.id].max = 10000000000000000;
                    } else {
                        questionnaire.survey.questions[filter.id].value = 'valuePercent';
                        questionnaire.survey.questions[filter.id].max = 1;
                    }

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

            var filtersIds = _.map($scope.tabs.filters, function(el) {
                return el.id;
            });
            var questionnairesIds = _.map($scope.tabs.questionnaires, function(el) {
                return el.id;
            });

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
                        questionnaires: _.filter(questionnairesIds,function(el) {
                            if (el) {
                                return true;
                            }
                        }).join(',')
                    }
                }).success(function(questionnaires)
                {
                    _.forEach($scope.tabs.questionnaires, function(scopeQuestionnaire) {
                        if (questionnaires[scopeQuestionnaire.id]) {
                            _.forEach(questionnaires[scopeQuestionnaire.id], function(values, filterId) {
                                scopeQuestionnaire.survey.questions[filterId].filter.values = values;
                            });
                        }
                    });

                    $scope.isComputing = false;
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

        questionnaire.isLoading = true;

        // get survey if exists or create
        getOrSaveSurvey(questionnaire).then(function(survey) {
            questionnaire.survey = survey;

            // create questionnaire
            saveUnitQuestionnaire(questionnaire).then(function(newQuestionnaire) {
                questionnaire.id = newQuestionnaire.id;
                questionnaire.isLoading = true;
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
                    var questionPromises = _.map(questionsForSave, function(question) {
                        var qdeferred = $q.defer();
                        getOrSaveQuestion(question).then(function(newQuestion) {
                            question = newQuestion;
                            qdeferred.resolve(question);
                            propagateSurvey(survey);

                            var answersForSave = _.filter(question.answers, function(a) {
                                if (a[question.value] || a[question.value] === 0) {
                                    return true;
                                }
                            });
                            _.forEach(answersForSave, function(answer, partId) {
                                answer.questionnaire = questionnaire.id;
                                answer.question = question.id;
                                answersPromises.push(createAnswer(question, answer, partId));
                            });

                        });
                        return qdeferred.promise;
                    });

                    // once all questions have been saved, notify next questionnaire to start saving
                    // and add listener on all answers promises to notify end of full saved questionnaire
                    $q.all(questionPromises).then(function() {
                        propagateSurvey(survey);
                        Qdeferred.notify('Questions recovered'); // says to promise listener he can save next questionnaire
                        $q.all(answersPromises).then(function() {
                            questionnaire.isLoading = false;
                            Qdeferred.resolve(questionnaire);
                        });
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
     * Recursive function that save all questionnaires
     * Instead of saving all questionnaires at the same time, wait for the questions to be saved in case a future questionnaire use the same survey and the same questions.
     * This avoid try to create questions twice and cause conflict.
     * @param questionnaires
     * @param index
     */
    var saveAllQuestionnairesWhenQuestionsAreSaved = function(questionnaires, index, sector) {
        if (questionnaires[index]) {
            saveCompleteQuestionnaire(questionnaires[index]).then(function() {
            }, function() {
            }, function() {
                saveAllQuestionnairesWhenQuestionsAreSaved(questionnaires, index + 1, sector);
            });

        } else {
            createQuestionnaireFilterUsages(sector);
        }
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
        if (code) {
            var c2 = _.isNumber(code) ? code : code.toUpperCase();
            var questionnaires = _.filter($scope.tabs.questionnaires, function(q) {
                if (q.survey.code) {
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
    var saveUnitQuestionnaire = function(questionnaire) {
        var deferred = $q.defer();

        if (_.isUndefined(questionnaire.id)) {

            // create a mini questionnaire object, to avoid big amounts of data to be sent to server
            var miniQuestionnaire = {
                dateObservationStart: questionnaire.survey.year + '-01-01',
                dateObservationEnd: questionnaire.survey.year + '-12-31',
                geoname: questionnaire.geoname.country.geoname.id,
                survey: questionnaire.survey.id
            };

            Restangular.all('questionnaire').post(miniQuestionnaire, {fields: 'permissions'}).then(function(newQuestionnaire) {
                questionnaire.id = newQuestionnaire.id;
                deferred.resolve(questionnaire);
            });
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
        if (population.initialValue === population.population) {
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
                Restangular.all('survey').post(survey).then(function(newSurvey) {
                    survey.id = newSurvey.id;
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
     * @param question
     * @param questionnaire
     */
    var updateAnswer = function(answer, question, questionnaire) {
        if (answer.id && (answer.permissions && answer.permissions.update || questionnaire && questionnaire.permissions && questionnaire.permissions.update)) {
            Restangular.restangularizeElement(null, answer, 'Answer');
            answer.isLoading = true;
            answer.put().then(function(newAnswer) {
                answer.isLoading = false;
                answer[question.value] = newAnswer[question.value];
                $scope.refresh(false, true);
            });
        }
    };

    /**
     * Update question considering questionnaire permissions
     * @param questionnaire
     * @param question
     */
    var updateQuestion = function(questionnaire, question) {
        if (question.id && questionnaire.permissions.update) {
            Restangular.restangularizeElement(null, question, 'question');
            question.put();
        }
    };

    /**
     * Delete answer considering answer permissions
     * @param question
     * @param answer
     */
    var deleteAnswer = function(question, answer) {
        if (answer.id && answer.permissions.delete) {
            answer.remove().then(function() {
                delete(answer.id);
                delete(answer[question.value]);
                delete(answer.edit);
                $scope.refresh(false, true);
            });
        }
    };

    /**
     * Create answer considering *questionnaire* permissions
     * @param question
     * @param answer
     * @param partId
     */
    var createAnswer = function(question, answer, partId) {
        var deferred = $q.defer();
        if (answer[question.value] || answer[question.value] === 0) {
            if (_.isUndefined(answer.part) && partId) {
                answer.part = partId;
            }

            answer.isLoading = true;
            Restangular.all('answer').post(answer, {fields: 'permissions'}).then(function(newAnswer) {
                answer.id = newAnswer.id;
                answer[question.value] = newAnswer[question.value];
                answer.isLoading = false;
                deferred.resolve(answer);
            });
        } else {
            deferred.reject('no value');
        }

        return deferred.promise;
    };

    /**
     * use question or save if necessary and return result
     * @param question
     */
    var getOrSaveQuestion = function(question) {
        var deferred = $q.defer();

        // if no id, create
        if (_.isUndefined(question.id)) {
            var miniQuestion = {};
            miniQuestion.name = question.name;
            miniQuestion.survey = question.survey;
            miniQuestion.filter = question.filter.id;
            miniQuestion.type = 'Numeric';
            miniQuestion.isAbsolute = question.isAbsolute;

            Restangular.all('question').post(miniQuestion).then(function(newQuestion) {
                question.id = newQuestion.id;
                deferred.resolve(question);
            });

            // else, do nothing
        } else {
            deferred.resolve(question);
        }

        return deferred.promise;
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
     * In case sector parameter is detected in url, this function ensures each filter has sub filters dedicated to filter data (Usualy people and equipement but may be anything)
     */
    var prepareSectorFilters = function() {
        if ($scope.tabs.filters && $scope.sector) {

            var sectorFilters = _.filter($scope.tabs.filters, function(f) {
                if (!f.sectorChild) {
                    return true;
                }
            });

            var sectorChildrenNames = $scope.sectorChildren.split(',');

            _.forEachRight(sectorFilters, function(filter) {
                var childSectorFilters = _.filter($scope.tabs.filters, function(f) {
                    if (f.parents && f.parents[0] && f.parents[0] == filter.id) {
                        return true;
                    }
                });
                if (childSectorFilters.length === 0) {
                    var sectorChildFilters = _.map(sectorChildrenNames, function(childSectorName, sectorIndex) {
                        return {
                            id: '_' + filter.id + "_" + sectorIndex,
                            name: childSectorName,
                            parents: [filter.id],
                            level: filter.level + 1,
                            sectorChild: true
                        };
                    });

                    var index = _.findIndex($scope.tabs.filters, {id: filter.id});
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
     * Save all filters, first root ones, then children ones
     * @returns promise
     */
    var saveFilters = function() {
        var deferred = $q.defer();

        // get all filters with starting by _1
        var parentFilters = _.filter($scope.tabs.filters, function(f) {
            if (/^_\d+/.test(f.id)) {
                return true;
            }
        });

        if (_.isEmpty(parentFilters)) {
            deferred.resolve();
        } else {
            saveFiltersCollection(parentFilters).then(function() {

                // get all filters with starting by __1
                var childrenFilters = _.filter($scope.tabs.filters, function(f) {
                    if (/^__\d+/.test(f.id)) {
                        return true;
                    }
                });
                if (!_.isEmpty(parentFilters)) {
                    saveFiltersCollection(childrenFilters).then(function() {
                        deferred.resolve();
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

        // get all filters with starting by _1 or __1
        if (filtersToSave.length === 0) {
            deferred.resolve();
        } else {
            var filterPromises = [];
            _.forEach(filtersToSave, function(filter) {
                filterPromises.push(saveFilter(filter));
            });
            $q.all(filterPromises).then(function() {
                $scope.expandHierarchy = true;
                $location.search('sectorChildren', null);
                $scope.sector = false;
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
        Restangular.all('filter').post({name: filter.name, parents: filter.parents, filterSets: filter.filterSets}).then(function(newFilter) {
            filter.id = newFilter.id;
            filter.isLoading = false;
            delete(filter.sector);
            delete(filter.sectorChild);
            replaceQuestionsIds(filter.id, filter.oldId);
            replaceIdReferenceOnChildFilters(filter);
            deferred.resolve(filter);
        });

        return deferred.promise;
    };

    /**
     * When saving a filter with temporary url, we need to update question filter and index with new filter id.
     * @param id
     * @param oldId
     */
    var replaceQuestionsIds = function(id, oldId) {
        _.forEach($scope.tabs.questionnaires, function(questionnaire) {
            if (questionnaire.survey && questionnaire.survey.questions && questionnaire.survey.questions[oldId]) {
                questionnaire.survey.questions[id] = questionnaire.survey.questions[oldId];
                questionnaire.survey.questions[id].filter.id = id;
                delete(questionnaire.survey.questions[oldId]);
            }
        });
    };

    /**
     * Remplace id related to filters that have temporary id by the new ID returned by DB.
     * @param filter
     */
    var replaceIdReferenceOnChildFilters = function(filter) {
        var children = _.filter($scope.tabs.filters, function(f) {
            if (f.parents && f.parents[0] && f.parents[0] == filter.oldId) {
                return true;
            }
        });
        _.forEach(children, function(child) {
            child.parents[0] = filter.id;
        });
    };

    /**
     * Create usages only if we are in sector mode
     * As we cancel sector mode at
     * @param execute
     */
    var createQuestionnaireFilterUsages = function(execute) {

        if (execute) {
            var rootFilters = _.filter($scope.tabs.filters, function(f) {
                if (f.parents[0] == $scope.tabs.filter.id) {
                    return true;
                }
            });
            var filters = _.map(rootFilters, function(filter) {
                var children = _.filter($scope.tabs.filters, function(f) {
                    if (f.parents[0] == filter.id) {
                        return true;
                    }
                });
                return filter.id + ':' + _.map(children,function(f) {
                    return f.id;
                }).join('-');
            });

            var questionnaires = [];
            _.forEach($scope.tabs.questionnaires, function(questionnaire) {
                questionnaire.filterQuestionnaireUsages = 1;
                if (questionnaire.id) {
                    questionnaires.push(questionnaire.id);
                }
            });

            listQuestionnairesWithFilterUsages($scope.tabs.questionnaires);

            $http.get('/api/filter/createUsages', {
                params: {
                    filters: filters.join(','),
                    questionnaires: questionnaires.join(',')
                }
            }).success(function()
            {
                $scope.firstQuestionnairesRetrieve = true;
                $scope.refresh(false, true);
            });

        }
    };

    /**
     * Questionnaires with usages
     * @param questionnaires
     */
    var listQuestionnairesWithFilterUsages = function(questionnaires) {
        $scope.questionnairesWithUsages = _.filter(questionnaires, function(q) {
            if (!_.isEmpty(q.filterQuestionnaireUsages) || _.isNumber(q.filterQuestionnaireUsages)) {
                return true;
            }
        });
    };

    /**
     * Update parameters on url exlucding empty ids to avoid multiple consecutive commas that cause problems on server side.
     * @param element
     */
    var updateUrl = function(element) {
        $location.search(element, _.filter(_.pluck($scope.tabs[element], 'id'),function(el) {
            if (el) {
                return true;
            }
        }).join(','));
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
