
angular.module('myApp').controller('Contribute/QuestionnaireCtrl', function($scope, $routeParams, Restangular, Answer) {
    'use strict';

    $scope.isJmp = true;
    $scope.questions = [];
    $scope.originalQuestions = []; // store original questions

    // If a questionnaire is specified in URL, load its data
    if ($routeParams.id) {

        $scope.isLoading = true;
        Restangular.one('questionnaire', $routeParams.id).all('question').getList({fields: 'type,filter,answers,choices,parts,isCompulsory,isMultiple,isFinal,chapter,description'}).then(function(questions) {
            var requiredNumberOfAnswers = 3;
            $scope.questions = questions;

            // Store copy of original object
            angular.forEach(questions, function(question) {

                if (question.type == 'Numeric') {

                    // Make sure we have the right number existing in the Model
                    var numberOfAnswers = question.answers.length;
                    if (numberOfAnswers < requiredNumberOfAnswers) {

                        // create an empty answer for the need of NgGrid
                        for (var index = 0; index < requiredNumberOfAnswers - numberOfAnswers; index++) {
                            question.answers.push({});
                        }
                    }
                    $scope.originalQuestions.push(Restangular.copy(question));
                }
                else {
                    $scope.isJmp = false;
                }
            });

            $scope.isLoading = false;
        });

    }

});
