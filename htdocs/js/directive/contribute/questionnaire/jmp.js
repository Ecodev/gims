
angular.module('myApp.directives').directive('gimsContributeQuestionnaireJmp', function() {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with element
        // This HTML will replace the directive.
        template: '<div ng-grid="gridOptions" class="gridStyle"></div>',
        // The linking function will add behavior to the template
        link: function(scope, element, attrs) {
            // nothing to do ?
        },
        controller: function($scope, $routeParams, Restangular, Answer, $timeout) {

            $scope.$watch('questions', function(questions)
            {
                // Re-index answers by their parts ID, instead of natural index
                if (questions.length > 0 && $scope.isJmp) {
                    angular.forEach(questions, function(question) {
                        var answersIndexedByPart = {};
                        angular.forEach(question.answers, function(answer) {
                            if (answer.part && answer.part.id) {
                                answersIndexedByPart[answer.part.id] = answer;
                            }
                        });
                        for (var i = 1; i < 4; i++) {
                            if (!answersIndexedByPart[i])
                                answersIndexedByPart[i] = {};
                        }
                        question.answers = answersIndexedByPart;
                    });
                }
            });

            // Validate Answer method
            $scope.validateAnswer = function(column, row) {

                var answerIndex = /[0-9]+/g.exec(column.field)[0];
                var answer = row.entity.answers[answerIndex];

                // Try detecting whether the user has typed .12 to be converted to 0.12
                var result = answer.valuePercent + '';
                if (result.charAt(0) === '.') {
                    result = '0' + result;
                    answer.valuePercent = result - 0;
                }

                // Set 0 value is user has entered fantasist values
                if (isNaN(parseInt(answer.valuePercent, 10))) {
                    answer.valuePercent = 0;
                }

                // Allowed value is between [0-1]
                if (answer.valuePercent >= 0 && answer.valuePercent <= 1) {
                    $('.col' + column.index, row.elm).find('input').removeClass('error');
                } else {
                    // Get the input field to wrap it with error div
                    $('.col' + column.index, row.elm).find('input').addClass('error');
                }
            };

            // Update Answer method
            $scope.updateAnswer = function(column, row) {

                var reg = new RegExp('[0-9]+', "g");
                var answerIndex = reg.exec(column.field)[0];
                var question = row.entity;

                var answer = new Answer(question.answers[answerIndex]);

                var reloadQuestion = function() {
                    question = Restangular.one('question', question.id).get({fields: 'filter,answers'});

                    // GUI remove the loading icon
                    $('.fa-loading', row.elm).toggle();
                };

                // Get the field and check whether it has an error class
                if (!$('.col' + column.index, row.elm).find('input').hasClass('error')) {

                    $('.col' + column.index, row.elm).css('backgroundColor', 'inherit');

                    // GUI display a loading icon
                    $('.fa-loading', row.elm).toggle();

                    // True means the answer exists and must be updated. Otherwise, create a new answer
                    if (answer.id > 0) {
                        answer.$update({id: answer.id}, reloadQuestion);
                    } else {
                        // Convention:
                        // the answerIndex == part
                        answer.part = answerIndex;

                        answer.question = question.id;
                        answer.questionnaire = $routeParams.id;
                        answer.$create(reloadQuestion);
                    }

                } else {
                    $('.col' + column.index, row.elm).css('backgroundColor', '#FF6461');
                }
            };

            // Template for cell editing with input "number".
            var cellEditableTemplate = '<input ng-class="\'colt\' + col.index" ng-input="COL_FIELD" ng-model="COL_FIELD" step="any" type="number" style="width: 90%" ng-blur="updateAnswer(col, row)" ng-keyup="validateAnswer(col, row)" />';

            // Keep track of the selected row.
            $scope.selectedRow = [];

            // Configure ng-grid.
            var gridLayoutPlugin = new ngGridLayoutPlugin();
            $scope.gridOptions = {
                data: 'questions',
                plugins: [gridLayoutPlugin],
                enableCellSelection: true,
                showFooter: false,
                selectedItems: $scope.selectedRow,
                multiSelect: false,
                columnDefs: [
                    {field: 'name', displayName: 'Question', width: '500px'},
                    {field: 'answers.1.valuePercent', displayName: 'Urban', enableCellEdit: true, cellFilter: 'percent', editableCellTemplate: cellEditableTemplate}, //, cellTemplate: 'cellTemplate.html'
                    {field: 'answers.2.valuePercent', displayName: 'Rural', enableCellEdit: true, cellFilter: 'percent', editableCellTemplate: cellEditableTemplate},
                    {field: 'answers.3.valuePercent', displayName: 'Total', enableCellEdit: true, cellFilter: 'percent', editableCellTemplate: cellEditableTemplate},
                    {displayName: '', cellTemplate: '<i class="fa fa-loading" style="display: none" />', cellClass: 'column-loading', width: '28px'}
                ]
            };

            // Here we need to tell ng-grid to refresh, because it was initially hidden
            // and it could layout properly
            $scope.$watch('isLoading', function() {
                $timeout(function() {
                    gridLayoutPlugin.updateGridLayout();
                }, 0);

            });

            // Counter of request being sent.
            $scope.sending = 0;

            // Update Data
            $scope.updateAnswers = function() {
                angular.forEach($scope.questions, function(question, key) {
                    var questionOriginal = $scope.originalQuestions[key];

                    // save the question only if it is different from the original
                    if (!angular.equals(question, questionOriginal)) {
                        $scope.sending = $scope.sending + question.answers.length;

                        // create an answer
                        angular.forEach(question.answers, function(answerObject) {
                            var answer = new Answer(answerObject);
                            answer.$update({id: answer.id}, function() {
                                $scope.sending--;
                            });
                        });
                    }
                });
            };

        }
    }
});
