angular.module('myApp').controller('Browse/FilterCtrl', function($scope, $routeParams, $location, $http, $timeout, Restangular, $q, authService, $route, $rootScope) {
    'use strict';

    /***************************************** First execution initialisation */

    if ($location.$$path.indexOf('/contribute') >= 0) {
        $scope.mode = 'Contribute';
    } else if ($location.$$path.indexOf('/browse') >= 0) {
        $scope.mode = 'Browse';
    }

    /************************************************ Variables initialisation */

    // params for ajax requests
    $scope.filterParams = {fields: 'paths,color,genericColor', itemOnce: 'true'};
    $scope.filterSetFields = {fields: 'color,paths'};
    $scope.filterFields = {fields: 'color,paths'};
    $scope.countryParams = {fields: 'geoname'};
    $scope.countryFields = {fields: 'geoname.questionnaires,geoname.questionnaires.survey,geoname.questionnaires.survey.questions,geoname.questionnaires.survey.questions.type,geoname.questionnaires.survey.questions.filter'};
    $scope.questionnaireWithQTypeFields = {fields: 'survey.questions,survey.questions.type'};
    $scope.questionnaireWithAnswersFields = {fields: 'permissions,geoname.country,survey.questions,survey.questions.filter,survey.questions.answers,survey.questions.answers.questionnaire,survey.questions.answers.part'};
    $scope.surveyFields = {fields: 'questionnaires.survey,questionnaires.survey.questions,questionnaires.survey.questions.type,questionnaires.survey.questions.filter'};


    // Variables initialisations
    $scope.isLoading = false;
    $scope.expandSelection = true;
    $scope.firstQuestionnairesRetrieve = false; // avoid to compute filters before questionnaires have been retrieved, getComputedFilters() need ready base to complete data
    $scope.selection = {};
    $scope.parts = Restangular.all('part').getList().$object;
    $scope.modes = ['Browse', 'Contribute'];
    $scope.dbSurveys = null;
    $scope.surveysTemplate = "[[item.code]] - [[item.name]]";
    $scope.filtersTemplate = "" +
        "<div>" +
            "<div class='col-sm-4 col-md-4 select-label select-label-with-icon'>" +
            "    <i class='fa fa-gims-filter' style='color:[[item.color]];' ></i> [[item.name]]" +
            "</div>" +
            "<div class='col-sm-7 col-md-7'>" +
            "    <small>" +
            "       [[_.map(item.paths, function(path){return \"<div class='select-label select-label-with-icon'><i class='fa fa-gims-filter'></i> \"+path+\"</div>\";}).join('')]]" +
            "    </small>" +
            "</div>" +
            "<div class='clearfix'></div>" +
        "</div>";

    /*************************************************************** Watchers */

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

    $scope.$watch('selection.filterSet', function() {
        if ($scope.selection.filterSet) {
            $scope.isLoading = true;
            Restangular.one('filterSet', $scope.selection.filterSet.id).getList('filters', _.merge($scope.filterSetFields, {perPage: 1000})).then(function(filters) {
                if (filters) {
                    $scope.selection.filters = filters;
                    $scope.selection.filter = null;
                }
                $scope.isLoading = false;
                checkSelectionExpand();
            });
        }
    });

    $scope.$watch('selection.filter', function() {
        if ($scope.selection.filter) {
            $scope.isLoading = true;
            Restangular.one('filter', $scope.selection.filter.id).getList('children', _.merge($scope.filterFields, {perPage: 1000})).then(function(filters) {
                if (filters) {
                    $scope.selection.filters = filters;
                    $scope.selection.filterSet = null;
                }
                $scope.isLoading = false;
                checkSelectionExpand();
            });
        }
    });

    $scope.$watch('selection.country', function() {
        if ($scope.selection.country) {
            $scope.isLoading = true;
            Restangular.one('country', $scope.selection.country.id).get(_.merge($scope.countryFields, {perPage: 1000})).then(function(country) {
                $scope.selection.questionnaires = country.geoname.questionnaires;
                $scope.selection.survey = null;
                $scope.isLoading = false;
                checkSelectionExpand();
            });
        }
    });

    $scope.$watch('selection.survey', function() {
        if ($scope.selection.survey) {
            $scope.isLoading = true;
            Restangular.one('survey', $scope.selection.survey.id).get(_.merge($scope.surveyFields, {perPage: 1000})).then(function(survey) {
                $scope.isLoading = false;
                $scope.selection.questionnaires = survey.questionnaires;
                $scope.selection.country = null;
                checkSelectionExpand();
            });
        }
    });

    var firstLoading = true;
    $scope.$watch('selection.filters', function() {
        getComputedFilters();
        if (firstLoading === true && $scope.selection.filters && $scope.selection.questionnaires) {
            checkSelectionExpand();
        }
    });

    $scope.$watch('selection.questionnaires', function(newQuests, oldQuests) {
        var newQuestionnaires = _.difference(_.pluck(newQuests, 'id'), _.pluck(oldQuests, 'id'));
        newQuestionnaires = newQuestionnaires ? newQuestionnaires : [];
        getQuestionnaires(newQuestionnaires, $scope.questionnaireWithQTypeFields, checkGlassQuestionnaires);

        if (firstLoading === true && $scope.selection.filters && $scope.selection.questionnaires) {
            checkSelectionExpand();
        }
    });

    /******************************************************** Scope functions */

    /**
     * Refreshing page means :
     *  - Recover all questionnaires permissions (in case user swith from browse to contribute/full view and need to be logged in)
     *  - Recompute filters, after some changes on answers. Can be done automatically after each answer change, but is heavy.
     * @param questionnaires
     */
    $scope.refresh = function(questionnairesPermissions, filtersComputing) {

        if (questionnairesPermissions && !_.isUndefined($scope.selection) && !_.isUndefined($scope.selection.questionnaires)) {
            getQuestionnaires($scope.selection.questionnaires, {fields: 'permissions'}, updateQuestionnairePermissions);
        }

        if (filtersComputing) {
            getComputedFilters();
        }
    };

    /**
     * Call api to get answer permissions
     * @param answer
     * @param callback
     */
    $scope.getPermissions = function(answer, callback) {

        if (answer.id && _.isUndefined(answer.permissions)) {
            Restangular.one('answer', answer.id).get({fields: 'permissions'}).then(function(newAnswer) {
                answer.permissions = newAnswer.permissions;

                // if value has been updated between permissions check, restore value
                if (!answer.permissions.update) {
                    answer.valuePercent = newAnswer.valuePercent;
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
        _.forEachRight($scope.selection.questionnaires, function(questionnaire) {
            if (_.isUndefined(questionnaire.id)) {
                isEmptyQuestionnaires = true;
                return false;
            }
        });
        return isEmptyQuestionnaires;
    };

    /**
     * Update an answer
     * Create an answer and if needed the question related
     * @param answer
     * @param question
     * @param filter
     * @param questionnairePermissions
     */
    $scope.saveAnswer = function(answer, question, filter, questionnaire, part) {

        // complete question in all situations with filtername if there is no name specified
        if (_.isUndefined(question.name)) {
            question.name = filter.name;
        }

        // avoid to do some job if the value is not changed or if it's invalid (undefined)
        if (answer.initialValue === answer.valuePercent || (_.isUndefined(answer.valuePercent) && !_.isUndefined(answer.initialValue))) {
            answer.valuePercent = answer.initialValue;
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
        if (answer.id && !$scope.toBoolNum(answer.valuePercent)) {
            $scope.removeAnswer(answer);

        // update
        } else if (answer.id) {

            if (answer.permissions) {
                updateAnswer(answer);
            } else {
                $scope.getPermissions(answer, updateAnswer);
            }

        // create answer, if allowed by questionnaire
        } else if (_.isUndefined(answer.id) && !_.isUndefined(answer.valuePercent) && questionnaire.permissions.create) {
            answer.isLoading = true;
            answer.questionnaire = questionnaire.id;
            question.survey = questionnaire.survey.id;
            // if question is not created, create it before creating the answer
            var questionPromise = getOrSaveQuestion(question);
            questionPromise.then(function(question) {
                answer.question = question.id;
                answer.part = part.id;
                var answerPromise = createAnswer(answer);
                answerPromise.then(function() {
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
        var checkPromise = $scope.checkQuestionnairesIntegrity();
        checkPromise.then(function() {
            if (_.isUndefined(index)) {
                _.forEach($scope.selection.questionnaires, function(questionnaire, index) {
                    saveCompleteQuestionnaire($scope.selection.questionnaires[index]);
                });
            } else {
                saveCompleteQuestionnaire($scope.selection.questionnaires[index]);
            }
        });
    };

    /**
     * Avoid new questionnaires to have the same country for a same survey and avoid a same survey code to have two different years
     */
    $scope.checkQuestionnairesIntegrity = function() {
        var deferred = $q.defer();

        // check for countries
        $timeout(function() {
            _.forEach($scope.selection.questionnaires, function(q1, i) {
                if (_.isUndefined(q1.id)) {
                    q1.errors = {
                        duplicateCountryCode: false,
                        codeAndYearDifferent: false,
                        countryAlreadyUsedForExistingSurvey : false
                    };

                    _.forEach($scope.selection.questionnaires, function(q2, j) {
                        if (_.isUndefined(q2.id) && i != j) {
                            if (q1.geoname && q2.geoname && q1.geoname.country.id == q2.geoname.country.id) {
                                if (q1.survey.code && q2.survey.code && q1.survey.code == q2.survey.code) {
                                    q1.errors.duplicateCountryCode = true;
                                }
                            } else {

                                if (!!q1.survey && !!q1.survey.year && !!q2.survey && !!q2.survey.year && q1.survey.year != q2.survey.year) {
                                    q1.errors.codeAndYearDifferent = true;
                                }
                            }
                        }
                    });
                }
                deferred.resolve();
            });
        }, 0);

        return deferred.promise;
    };

    /**
     * Remove question after retrieving permissions from server if not yet done
     * @param answer
     */
    $scope.removeAnswer = function(answer) {
        Restangular.restangularizeElement(null, answer, 'answer');
        if (_.isUndefined(answer.permissions)) {
            $scope.getPermissions(answer, deleteAnswer);
        } else {
            deleteAnswer(answer);
        }
    };

    /**
     * Add column (questionnaire)
     */
    $scope.addQuestionnaire = function() {
        var emptyQuestionnaire = {
            survey: {},
            permissions: {
                create: true
            }
        };

        var emptyQuestions = {};
        _.forEach($scope.selection.filters, function(filter) {

            var emptyAnswers = {};
            _.forEach($scope.parts, function(part) {
                emptyAnswers[part.id] = {};
            });

            emptyQuestions[filter.id] = {
                filter: {
                    id: filter.id
                },
                answers: emptyAnswers
            };
        });

        emptyQuestionnaire.survey.questions = emptyQuestions;

        if (_.isUndefined($scope.selection.questionnaires)) {
            $scope.selection.questionnaires = [];
        }

        $scope.selection.questionnaires.push(emptyQuestionnaire);
    };

    /**
     * Remove column (questionnaire)
     * @param index
     */
    $scope.removeQuestionnaire = function(index) {
        $scope.selection.questionnaires.splice(index, 1);
        updateUrl('questionnaires');
    };

    /**
     * Remove row (filter)
     * @param index
     */
    $scope.removeFilter = function(index) {
        $scope.selection.filters.splice(index, 1);
        updateUrl('filters');
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
     * @param answer
     */
    $scope.setInitialValue = function(answer) {
        answer.initialValue = answer.valuePercent;
    };

    $scope.toBoolNum = function(val) {
        if (_.isNumber(val) && val >= 0) {
            return true;
        }
        return false;
    };


    $scope.saveQuestion = function(question){
        if (question.id && !_.isEmpty(question.name) && question.name != question.initialName) {
            question.type = 'Numeric';
            Restangular.restangularizeElement(null, 'question', question);
            $scope.isLoading = true;
            Restangular.one('question').put({id:question.id, name:question.name}).then(function(){
                $scope.isLoading = false;
            });
        }
    };

    /****************************************************** Private functions */

    /**
     * Call questionnaires asking for passed fields and executing callback function passing received questionnaires
     * @param questionnaires
     * @param fields
     * @param callback
     */
    var getQuestionnaires = function(questionnaires, fields, callback) {
        if (questionnaires.length === 1 && !_.isUndefined(questionnaires[0])) {
            $scope.isLoading = true;
            Restangular.one('questionnaire', questionnaires[0]).get(fields).then(function(questionnaire) {
                callback([questionnaire]);
                $scope.isLoading = false;
            });
        } else if (questionnaires.length > 1) {
            $scope.isLoading = true;
            Restangular.all('questionnaire').getList(_.merge({id: questionnaires.join(',')}, fields)).then(function(questionnaires) {
                callback(questionnaires);
                $scope.isLoading = false;
            });
        }
    };

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
        // no action is possible on multiple glass questionnaires (neigther browse or contribute)
        if (glass.length === 1 && $scope.selection.questionnaires.length === 1) {
            $location.url('/contribute/questionnaire/glass/' + glass[0].id + "?returnUrl=" + $location.path());

            // else list glass questionnaires apart
        } else {
            $scope.selection.glass = glass;

            // remove glass questionnaires from selected questionnaires
            $scope.selection.questionnaires = _.filter($scope.selection.questionnaires, function(q) {
                if (!_.find(glass, {id: q.id})) {
                    return true;
                }
                return false;
            });

            // get data for new jmp questionnaires
            getQuestionnaires(_.pluck(jmp, 'id'), $scope.questionnaireWithAnswersFields, function(questionnaires) {
                $scope.firstQuestionnairesRetrieve = true;
                prepareQuestionnaires(questionnaires);
            });
        }
    };

    /**
     * Init answers
     * @param questionnaires
     */
    var prepareQuestionnaires = function(questionnaires) {
        angular.forEach(questionnaires, function(questionnaire) {
            var questions = {};
            _.forEach(questionnaire.survey.questions, function(question) {
                // class answers by part id
                var answers = {};
                _.forEach(question.answers, function(answer) {
                    if (!_.isUndefined(answer.questionnaire) && answer.questionnaire.id == questionnaire.id) {
                        delete(answer.questionnaire);
                        answers[answer.part.id] = answer;
                    }
                });

                // grants an empty object to receive answer value (by ng-model)
                _.forEach($scope.parts, function(part) {
                    answers[part.id] = getEmptyAnswer(answers[part.id], questionnaire.id, question.id, part.id);
                });

                question.answers = answers;
                questions[question.filter.id] = question;
            });

            questionnaire.survey.questions = questions;

            // update $scope with modified questionnaire
            $scope.selection.questionnaires[_.findIndex($scope.selection.questionnaires, {id: questionnaire.id})] = questionnaire;
        });

        getComputedFilters();
    };

    /**
     * Update questionnaires permissions
     * @type Function
     * @param questionnaires
     */
    var updateQuestionnairePermissions = function(questionnaires) {
        _.forEach($scope.selection.questionnaires, function(questionnaire) {
            questionnaire.permissions = _.find(questionnaires, {id: questionnaire.id}).permissions;
        });
    };

    /**
     * Init computed filters
     * @type {null}
     */
    var getComputedFiltersCanceller = null;
    var getComputedFilters = function() {

        if (!$scope.firstQuestionnairesRetrieve) {
            return;
        }

        $timeout(function() {
            var filtersIds = _.map($scope.selection.filters, function(el) {
                return el.id;
            });
            var questionnairesIds = _.map($scope.selection.questionnaires, function(el) {
                return el.id;
            });

            if (filtersIds.length > 0 && questionnairesIds.length > 0) {
                $scope.isLoading = true;
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
                            if (el) {return el;}
                        }).join(',')
                    }
                }).success(function(questionnaires) {
                        _.forEach(questionnaires, function(filters, questionnaireId) {
                            _.forEach(filters, function(values, filterId) {
                                _.forEach($scope.selection.questionnaires, function(scopeQuestionnaire) {
                                    if (scopeQuestionnaire.id == questionnaireId) {
                                        if (_.isUndefined(scopeQuestionnaire.survey.questions[filterId])) {
                                            scopeQuestionnaire.survey.questions[filterId] = {type: 'Numeric'};
                                        }

                                        if (_.isUndefined(scopeQuestionnaire.survey.questions[filterId].filter)) {
                                            scopeQuestionnaire.survey.questions[filterId].filter = {id: filterId};
                                        }
                                        scopeQuestionnaire.survey.questions[filterId].filter.values = values;
                                        scopeQuestionnaire.survey.questions[filterId].survey = scopeQuestionnaire.survey.id;

                                        if (_.isUndefined(scopeQuestionnaire.survey.questions[filterId].answers)) {
                                            scopeQuestionnaire.survey.questions[filterId].answers = {};
                                        }

                                        _.forEach($scope.parts, function(part) {
                                            scopeQuestionnaire.survey.questions[filterId].answers[part.id] = getEmptyAnswer(scopeQuestionnaire.survey.questions[filterId].answers[part.id], questionnaireId, null, part.id);
                                        });

                                    }
                                });
                            });
                        });

                        $scope.isLoading = false;
                        $scope.isComputing = false;
                    });
            }
        }, 0);
    };

    /**
     * Create a single questionnnaire
     * @param questionnaire
     */
    var saveCompleteQuestionnaire = function(questionnaire) {
        if (_.isUndefined(questionnaire.id) &&
            !_.isUndefined(questionnaire.geoname) &&
            !_.isUndefined(questionnaire.geoname.country)  &&
            !_.isUndefined(questionnaire.survey) &&
            !_.isEmpty(questionnaire.survey.code) &&
            !_.isUndefined(questionnaire.survey.year) &&
            questionnaire.errors.duplicateCountryCode === false &&
            questionnaire.errors.codeAndYearDifferent === false
            ) {
            questionnaire.isLoading = true;

            // get survey if exists or create
            var surveyPromise = getOrSaveSurvey(questionnaire);
            surveyPromise.then(function(survey) {
                questionnaire.survey = survey;

                // create questionnaire
                var questionnairePromise = saveUnitQuestionnaire(questionnaire);
                questionnairePromise.then(function(newQuestionnaire) {
                    questionnaire = newQuestionnaire;
                    questionnaire.isLoading = false;
                    updateUrl('questionnaires');

                    // create questions
                    _.forEach(questionnaire.survey.questions, function(question) {
                        if (!_.isUndefined(question.name)) {
                            var questionPromise = getOrSaveQuestion(question);
                            questionPromise.then(function(newQuestion) {
                                question = newQuestion;
                                _.forEach(question.answers, function(answer, partId) {
                                    answer.questionnaire = questionnaire.id;
                                    createAnswer(answer, partId);
                                });
                            });
                        }
                    });
                });

            // reject for survey creation
            }, function(){
                questionnaire.isLoading = false;
            });
        }
    };

    var saveUnitQuestionnaire = function(questionnaire) {
        var deferred = $q.defer();

        if (_.isUndefined(questionnaire.id)) {

            // create a mini questionnaire object, to avoid big amounts of data to be sent to server
            var miniQuestionnaire = {};
            miniQuestionnaire.dateObservationStart = questionnaire.survey.year + '-01-01';
            miniQuestionnaire.dateObservationEnd = questionnaire.survey.year + '-12-31';
            miniQuestionnaire.geoname = questionnaire.geoname.country.geoname.id;
            miniQuestionnaire.survey = questionnaire.survey.id;

            Restangular.all('questionnaire').post(miniQuestionnaire, {fields: 'permissions'}).then(function(newQuestionnaire) {
                questionnaire.id = newQuestionnaire.id;
                deferred.resolve(questionnaire);
            });
        }

        return deferred.promise;
    };

    /**
     * Get survey, or create it if dont exist
     * @param survey
     */
    var getOrSaveSurvey = function(questionnaire) {
        var deferred = $q.defer();
        var survey = questionnaire.survey;

        var surveyPromise = getSurveys(questionnaire);
        surveyPromise.then(

        // catch resolve result
        function(newSurvey){

            // a survey exists
            if (newSurvey) {
                survey.id = newSurvey.id;

                // init current questions id and names to match with those in the existing survey
                _.forEach(newSurvey.questions, function(question){
                    if (!_.isUndefined(survey.questions[question.filter.id])) {
                        survey.questions[question.filter.id].id = question.id;
                        survey.questions[question.filter.id].name = question.name;
                    }
                });
                deferred.resolve(survey);

            // no survey exists
            } else {
                survey.name = survey.code + " - " + survey.year;
                Restangular.all('survey').post(survey).then(function(newSurvey) {
                    survey.id = newSurvey.id;

                    // update javascript object with relations (id or object)
                    _.forEach(survey.questions, function(question) {
                        question.survey = survey.id;
                    });

                    deferred.resolve(survey);
                });
            }

        // catch reject result
        }, function() {
            if (_.isUndefined(questionnaire.errors)){
                questionnaire.errors = {};
            }
            questionnaire.errors.countryAlreadyUsedForExistingSurvey = true;
            deferred.reject('country already used in this survey');
        });

        return deferred.promise;
    };

    /**
     * This function recovers surveys by searching with Q params
     * If there is similar code, search if the country is already used
     * @param questionnaire
     * @returns null|survey Return null if no survey exists, returns the survey if exist or reject promise if country already used
     */
    var getSurveys = function(questionnaire){
        var deferred = $q.defer();
        var survey = questionnaire.survey;

        Restangular.all('survey').getList({q: survey.code, fields:'questions,questions.filter,questionnaires,questionnaires.geoname,questionnaires.geoname.country'}).then(function(surveys) {
            if (surveys.length === 0){
                deferred.resolve(null);
            } else {
                _.forEach(surveys, function(newSurvey){
                    if (newSurvey.code.toUpperCase() == survey.code.toUpperCase()) {
                        var countryAlreadyUsed = false;
                        _.forEach(newSurvey.questionnaires, function(newQuestionnaire){
                            if (questionnaire.geoname.country.id == newQuestionnaire.geoname.country.id) {
                                countryAlreadyUsed = true;
                                return false;
                            }
                        });

                        if(countryAlreadyUsed) {
                            deferred.reject();
                        } else {
                            deferred.resolve(newSurvey);
                        }
                    } else {
                        deferred.resolve(null);
                    }
                });
            }
        });

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
     */
    var updateAnswer = function(answer) {
        if (answer.id && answer.permissions.update) {
            answer.isLoading = true;
            answer.put().then(function() {
                answer.isLoading = false;
                $scope.refresh(false, true);
            });
        }
    };

    /**
     * Delete answer considering answer permissions
     * @param answer
     */
    var deleteAnswer = function(answer) {
        if (answer.id && answer.permissions.delete) {
            answer.remove().then(function() {
                delete(answer.id);
                delete(answer.valuePercent);
                delete(answer.edit);
                $scope.refresh(false, true);
            });
        }
    };

    /**
     * Create answer considering *questionnaire* permissions
     * @param answer
     */
    var createAnswer = function(answer, partId) {
        var deferred = $q.defer();

        if (!_.isUndefined(answer.valuePercent) && !_.isNull(answer.valuePercent)) {
            if (!_.isUndefined(partId)) {
                answer.part = partId;
            }

            answer.isLoading = true;
            Restangular.all('answer').post(answer, {fields: 'permissions'}).then(function(newAnswer) {
                answer.id = newAnswer.id;
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

        if (_.isUndefined(question.id)) {
            var miniQuestion = {};
            miniQuestion.name = question.name;
            miniQuestion.survey = question.survey;
            miniQuestion.filter = question.filter.id;
            miniQuestion.type = 'Numeric';

            Restangular.all('question').post(miniQuestion).then(function(newQuestion) {
                question.id = newQuestion.id;
                _.forEach(question.answers, function(answer) {
                    answer.question = question.id;
                });
                deferred.resolve(question);
            });

        } else {
            _.forEach(question.answers, function(answer) {
                answer.question = question.id;
            });
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
     *  If there are filter and questionnaires selected after this manipulatino
     *
     *  Don't hide selection panes if select with free selection tool on "Selected" tab.
     *
     *  The button "Expand/Compress Selection" reflects this status and allow to change is again.
     */
    var checkSelectionExpand = function() {
        firstLoading = false;
        if ($scope.selection.filters && $scope.selection.filters.length && $scope.selection.questionnaires && $scope.selection.questionnaires.length) {
            $scope.expandSelection = false;
        } else {
            $scope.expandSelection = true;
        }
    };


    var updateUrl = function(element){
        $location.search(element, _.filter(_.pluck($scope.selection[element], 'id'),function(el) {
            if (el) {
                return el;
            }
        }).join(','));
    };

    /* Redirect functions */
    var redirect = function() {
        $location.url($location.search().returnUrl);
    };

    $scope.cancel = function() {
        redirect();
    };

});
