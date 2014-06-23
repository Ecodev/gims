/* Controllers */
angular.module('myApp').controller('Admin/Question/CrudCtrl', function($scope, $routeParams, $location, Restangular, Modal) {
    "use strict";

    // Default redirect
    var questionFields = {fields: 'metadata,filter,survey,type,choices,parts,chapter,isCompulsory,isPopulation,isMultiple,isFinal,description,questions,isAbsolute,alternateNames'};
    var returnUrl = '/';

    $scope.sending = false;

    // @TODO : manage value null and integer value
    $scope.percentages = [
        {text: '100%', value: '1.000'},
        {text: '90%', value: '0.900'},
        {text: '80%', value: '0.800'},
        {text: '70%', value: '0.700'},
        {text: '60%', value: '0.600'},
        {text: '50%', value: '0.500'},
        {text: '40%', value: '0.400'},
        {text: '30%', value: '0.300'},
        {text: '20%', value: '0.200'},
        {text: '10%', value: '0.100'},
        {text: '0%', value: '0.000'}
    ];

    $scope.initChoices = function()
    {
        $scope.isChoices = false;
        $scope.isChapter = false;

        if ($scope.question.type == 'Choice') {
            $scope.isChoices = true;

            // If we have no choices list at all, then we save the existing
            // question with its new type to reload actual list of choices
            if (!$scope.question.choices && $scope.question.id) {
                $scope.question.choices = [{}];
                $scope.save();

            } else if (!$scope.question.choices || $scope.question.choices.length === 0) {
                // Otherwise, if the question is new and no choices exists, we inject an empty one

                $scope.question.choices = [{}];
            }
        }
        if ($scope.question.type == 'Chapter') {
            $scope.isChapter = true;
        }
    };

    $scope.removeChapter = function()
    {
        $scope.question.chapter = null;
    };

    $scope.addOption = function() {
        $scope.question.choices.push({});
    };

    $scope.deleteOption = function(index) {
        $scope.question.choices.splice(index, 1);
    };

    if ($routeParams.returnUrl) {
        returnUrl = $routeParams.returnUrl;
    }

    var redirect = function() {
        $location.url(returnUrl);
    };

    $scope.cancel = function() {
        redirect();
    };

    $scope.saveAndClose = function() {
        this.save(true);
    };
    $scope.save = function(redirectAfterSave) {
        $scope.sending = true;

        // First case is for update a question, second is for creating
        if ($scope.question.filter) {
            $scope.question.filter = $scope.question.filter.id;
        }

        if ($scope.question.chapter) {
            $scope.question.chapter = $scope.question.chapter.id;
        }

        if ($scope.question.id) {

            // if change type from Choice to another, remove choices from DB
            if ($scope.question.type != 'Choice' && $scope.question.choices && $scope.question.choices.length > 0) {
                delete $scope.question.choices;
            }

            $scope.question.put(questionFields).then(function(question) {
                $scope.sending = false;
                $scope.question = question;
                $scope.questions = question.questions;
                $scope.initChoices();
                if (redirectAfterSave) {
                    redirect();
                }
            }, function() {
                $scope.sending = false;
            });
        } else {
            $scope.question.survey = $routeParams.survey;

            delete $scope.question.sorting; // let the server define the sorting value
            Restangular.all('question').post($scope.question).then(function(question) {
                $scope.sending = false;

                if (redirectAfterSave) {
                    redirect();
                } else {
                    // redirect to edit URL
                    $location.path(sprintf('admin/question/edit/%s', question.id));
                }
            }, function() {
                $scope.sending = false;
            });
        }
    };

    $scope.chapterList = [];

    var setParentQuestions = function(surveyId) {
        Restangular.one('survey', surveyId).all('question').getList({fields: 'chapter,level,type', perPage: 1000}).then(function(questions) {

            angular.forEach(questions, function(question) {
                if (question.type == 'Chapter') {
                    var spacer = '';
                    for (var i = 1; i <= question.level; i++) {
                        spacer += "-- ";
                    }
                    $scope.chapterList.push({id: question.id, name: spacer + " " + question.name});
                }
            });
        });
    };

    // Delete a question
    $scope.delete = function() {
        Modal.confirmDelete($scope.question, {label: $scope.question.name, returnUrl: $location.search().returnUrl});
    };

    $scope.addAlternateName = function() {
        if (!$scope.question.alternateNames[$scope.question.questionnaireForAlternateNames.id]) {
            $scope.question.alternateNames[$scope.question.questionnaireForAlternateNames.id] = '';
            $scope.question.questionnaireForAlternateNames = null;
        }
    };

    $scope.deleteAlternate = function(questionnaireId) {
        delete $scope.question.alternateNames[questionnaireId];
    };

    $scope.$watch('[question.alternateNames, questionnaires]', function() {
        if (!$scope.questionnaires) {
            return;
        }

        $scope.notUsedQuestionnaires = _.filter($scope.questionnaires, function(q, id) {
            return _.isUndefined($scope.question.alternateNames[id]);
        });
    }, true);

    // Try loading question if possible...
    if ($routeParams.id) {
        Restangular.one('question', $routeParams.id).get(questionFields).then(function(question) {
            $scope.question = question;
            $scope.survey = question.survey;
            setParentQuestions($scope.question.survey.id);
            $scope.initChoices();
            if (_.isEmpty(question.alternateNames)) {
                question.alternateNames = {};
            }
            angular.forEach(question.questions, function(question) {
                question = Restangular.restangularizeElement(null, question, 'question');
            });
            $scope.questions = question.questions;

            Restangular.one('survey', $scope.question.survey.id).all('questionnaire').getList().then(function(questionnaires) {
                $scope.questionnaires = _.indexBy(questionnaires, 'id');
            });
        });

    } else {
        $scope.question = {};
        setParentQuestions($routeParams.survey);
    }

    Restangular.all('questionType').getList().then(function(types) {
        $scope.types = types;
    });

    // Load survey if possible
    var params = $location.search();
    if (params.survey !== undefined) {
        $scope.survey = Restangular.one('survey', params.survey).get().$object;
    }
});
