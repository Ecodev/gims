angular.module('myApp').controller('Contribute/QuestionnaireCtrl', function($scope, $routeParams, Restangular) {
    'use strict';

    $scope.isJmp = true;
    $scope.questions = [];
    $scope.questionnaireQueryParams = {permission: 'update', fields: 'permissions'};

    // If a questionnaire is specified in URL, load its data
    if ($routeParams.id) {

        $scope.isLoading = true;


        Restangular.one('questionnaire', $routeParams.id).all('question').getList({fields: 'type,filter,filter.isOfficial,answers,isCompulsory,choices,parts,isMultiple,isFinal,chapter,description'}).then(function(questions) {

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

                angular.forEach(questions, function(question) {

                    // First, re-index existing answers by their part ID
                    var answersIndexedByPart = {};
                    angular.forEach(question.answers, function(answer) {
                        if (answer.part && answer.part.id) {
                            answer = Restangular.restangularizeElement(null, answer, 'answer');
                            answersIndexedByPart[answer.part.id] = answer;
                        }
                    });

                    // Then, make sure sure that every part has one answer (create blank answer)
                    angular.forEach(question.parts, function(part) {
                        if (!answersIndexedByPart[part.id]) {
                            var blankAnswer = {
                                part: part.id,
                                question: question.id,
                                questionnaire: $routeParams.id
                            };

                            answersIndexedByPart[part.id] = blankAnswer;
                        }
                    });

                    question.answers = answersIndexedByPart;
                });
            }

            $scope.isLoading = false;
        });

    }

});
