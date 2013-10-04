angular.module('myApp').controller('Contribute/QuestionnaireCtrl', function($scope, $routeParams, Restangular, Answer) {
    'use strict';

    $scope.isJmp = true;
    $scope.questions = [];
    $scope.originalQuestions = []; // store original questions
    $scope.questionnaireQueryParams = {permission: 'update',fields: 'permissions'};

    // If a questionnaire is specified in URL, load its data
    if ($routeParams.id) {

        $scope.isLoading = true;


        Restangular.one('questionnaire', $routeParams.id).all('question').getList({fields:'type,filter,filter.isOfficial,answers,isCompulsory,choices,parts,isMultiple,isFinal,chapter,description'}).then(function(questions) {

            $scope.questions = questions;

            // test if jmp
            angular.forEach(questions, function(question) {
                if (question.type != 'Numeric') {
                    $scope.isJmp = false;
                    return;
                }
            });

            // if jmp, do some task that should be avoided in glass
            if ($scope.isJmp) {

                // Store copy of original object
                angular.forEach(questions, function(question) {
                    var requiredNumberOfAnswers = question.parts.length;

                    // Make sure we have the right number existing in the Model
                    var numberOfAnswers = question.answers.length;
                    if (numberOfAnswers < requiredNumberOfAnswers) {

                        // create an empty answer for the need of NgGrid
                        for (var index = 0; index < requiredNumberOfAnswers - numberOfAnswers; index++) {
                            question.answers.push({});
                        }
                    }
                    $scope.originalQuestions.push(Restangular.copy(question));
                });
            }

            $scope.isLoading = false;
        });

    }

});
