angular.module('myApp').controller('Contribute/GlaasCtrl', function($scope, $routeParams, $location, Restangular, QuestionAssistant) {
    'use strict';

    $scope.questionnaireQueryParams = {permission: 'update', fields: 'permissions', surveyType: 'glaas'};

    // If a questionnaire is specified in URL, load its data
    if ($routeParams.id) {
        $scope.isLoading = true;
        Restangular.one('questionnaire', $routeParams.id).all('question').getList({perPage: 1000, fields: 'type,filter,answers,isCompulsory,choices,parts,isMultiple,isFinal,chapter,description'}).then(function(questions) {
            $scope.questions = questions;
            $scope.isLoading = false;
        });
    }

    $scope.navigation = []; // used in next + previous buttons
    $scope.hierarchicQuestions = []; // used in hierarchic menu
    $scope.currentIndex = 0;
    $scope.currentQuestion = 0;
    $scope.index = {}; // indexed answers
    $scope.score = {};

    $scope.$watch(function() {
        return $location.url();
    }, function() {
        $scope.returnUrl = $location.search().returnUrl;
        $scope.currentUrl = encodeURIComponent($location.url());
    });

    $scope.$watch('questionnaire', function() {
        $scope.initializeQuestionnaire();
    });

    $scope.$watch('questions', function() {
        $scope.initializeQuestionnaire();
    });

    $scope.$watch('currentIndex', function(newIndex, old) {
        if (newIndex !== old) {
            $scope.refreshQuestion();
        }
    });

    var questionnaire2 = null;
    $scope.initializeQuestionnaire = function() {
        if ($scope.questionnaire && $scope.questions && $scope.questions.length > 0) {

            var questionnaire2 = _.cloneDeep($scope.questionnaire);
            questionnaire2.level = -1;
            questionnaire2.index = -1;
            questionnaire2.statusCode = 4;

            $scope.questions[0].active = true;

            angular.forEach($scope.questions, function(question, index) {

                // assign key to each question -> navigation menu bar uses it to avoid loops
                question.index = index;

                // prepare navigation
                question.hasFinalParentChapters = $scope.hasFinalParentChapters(index);
                if (question && question.type && !question.hasFinalParentChapters) {
                    question.navIndex = $scope.navigation.length;
                    $scope.navigation.push(question);
                }

                // restangularize answers
                angular.forEach(question.answers, function(answer) {
                    answer = Restangular.restangularizeElement(null, answer, 'answer');
                });
            });

            // preparing hierarchic questions : used for nav and for validation form
            $scope.hierarchicQuestions = $scope.getChildren(questionnaire2, $scope.questions);
            questionnaire2.children = $scope.hierarchicQuestions;
            QuestionAssistant.updateQuestion(questionnaire2, $scope.index, true);
            $scope.refreshQuestion();
        }
    };

    $scope.markQuestionnaireAs = function(newStatus) {
        if (questionnaire2.statusCode == 2 || questionnaire2.statusCode == 3) {
            if (newStatus === 'completed' && questionnaire2.status === 'new' || newStatus === 'validated' && questionnaire2.permissions.validate && questionnaire2.status === 'completed') {
                questionnaire2.status = newStatus;

                // -> cyclic structure error -> remove children
                var children = questionnaire2.children;
                delete questionnaire2.children;

                questionnaire2.put().then(function(questionnaire) {
                    questionnaire2.status = questionnaire.status;
                    questionnaire2.children = children;
                });

            }
        }
    };

    /**
     *
     * @param question that may contain children
     * @param list a flat array with list of all questions
     * @returns {Array} a list of children
     */
    $scope.getChildren = function(question, list) {
        var elements = [];
        if (list && list.length > 0) {
            for (var index = question.index + 1; index < list.length; index++) {
                var testedQuestion = list[index];

                // si même profondeur ou inférieure, inutile de poursuivre, c'est la fin du chapitre
                if (testedQuestion.level <= question.level) {
                    return elements;
                }
                // si profondeur plus grande de 1 = enfant
                if (testedQuestion.level == question.level + 1) {
                    testedQuestion.parent = question;
                    elements.push(testedQuestion);
                }

                if (testedQuestion.level >= question.level + 1) {
                    testedQuestion.children = $scope.getChildren(testedQuestion, list);
                }
            }

            return elements;
        }
    };

    $scope.goToPrintMode = function() {
        var i = 0;
        for (i; i < $scope.navigation.length; i++) {
            if ($scope.navigation[i].level === 0 && ($scope.navigation[i].activeParent || $scope.navigation[i].active)) {
                $scope.navigation[i].isFinal = true;
                if (i == $scope.currentIndex) {
                    $scope.refreshQuestion();
                } else {
                    $scope.currentIndex = $scope.navigation[i].navIndex;
                }
                break;
            }
        }
        setTimeout(function() {
            window.print();
            $scope.navigation[i].isFinal = false;
            $scope.refreshQuestion();
        }, 1500);
    };

    $scope.refreshQuestion = function() {
        var i;
        $scope.currentQuestion = $scope.navigation[$scope.currentIndex];
        // if question is chapter, retrieve all the subquestions that are contained in the chapter for display.
        if ($scope.currentQuestion.isFinal) {
            var children = [];
            for (i = $scope.currentQuestion.index + 1; i < $scope.questions.length; ++i) {
                var testedQuestion = $scope.questions[i];
                if (testedQuestion.level > $scope.currentQuestion.level) {
                    children.push(testedQuestion);
                } else {
                    break;
                }
            }
            $scope.currentQuestionChildren = children;

            // if question is not a chapter, there is no subquestions
        } else {
            $scope.currentQuestionChildren = [];
        }

        // retrieve all parent chapter to display name and description
        $scope.parentChapters = [];
        var firstChapterPerLevel = $scope.getListOfFirstChapterPerLevel($scope.currentQuestion.index, $scope.questions);
        for (var q in firstChapterPerLevel) {
            $scope.parentChapters.push($scope.questions[firstChapterPerLevel[q]]);
        }

        // Update nav (active and active_parent class)
        for (var id in $scope.navigation) {
            var question = $scope.navigation[id];
            question.active = false;
            question.activeParent = false;
        }

        firstChapterPerLevel = $scope.getListOfFirstChapterPerLevel($scope.currentIndex, $scope.navigation);
        for (i = 0; i < firstChapterPerLevel.length; i++) {
            $scope.navigation[firstChapterPerLevel[i]].activeParent = true;
        }
        $scope.currentQuestion.active = true;
    };

    /**
     *  Navigation
     *  */
    $scope.goToNext = function() {
        if ($scope.currentIndex < $scope.navigation.length - 1) {
            $scope.currentIndex = $scope.currentIndex + 1;
        }
    };

    $scope.goToPrevious = function() {
        if ($scope.currentIndex > 0) {
            $scope.currentIndex = $scope.currentIndex - 1;
        }
    };

    $scope.goTo = function(wantedIndex) {
        if (Number(wantedIndex) >= 0) {
            $scope.currentIndex = wantedIndex;
        }
    };

    $scope.getListOfFirstChapterPerLevel = function(startIndex, questions) {
        var askedQuestion = questions[startIndex];
        var firstChapterPerLevel = [];
        for (var j = startIndex; j >= 0; j--) { // go rewind until first question or first zero leveled question
            var testedQuestion = questions[j];
            if (testedQuestion.type === 'Chapter' && testedQuestion.level < askedQuestion.level && !firstChapterPerLevel[testedQuestion.level]) {
                firstChapterPerLevel[testedQuestion.level] = j; // sets the first chapter encontered each level
            }
            if (testedQuestion.level === 0) {
                break;
            }
        }
        return firstChapterPerLevel;
    };

    $scope.hasFinalParentChapters = function(index) {
        var listOfParentChapters = $scope.getListOfFirstChapterPerLevel(index, $scope.questions);
        for (var i in listOfParentChapters) {
            if ($scope.questions[listOfParentChapters[i]].isFinal) {
                return true;
            }
        }
        return false;
    };

    /* Redirect functions */
    var redirect = function() {
        $location.url($location.search().returnUrl);
    };

    $scope.cancel = function() {
        redirect();
    };

});
