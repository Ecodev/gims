
angular.module('myApp').controller('Contribute/QuestionnaireCtrl', function($scope, $routeParams, Restangular, Answer) {
    'use strict';

    var cellEditableTemplate, numberOfAnswers, requiredNumberOfAnswers;

    $scope.questions = [];
    $scope.originalQuestions = []; // store original questions

    // If a questionnaire is specified in URL, load its data
    if ($routeParams.id) {


        /**
         * Initialization of Glass questionnaire
         * Comment next two lines.
         */
        Restangular.one('questionnaire', $routeParams.id).all('question').getList({fields: 'type,filter,answers,choices,parts,compulsory,multiple,final,chapter,description'}).then(function(questions) {
            $scope.originalQuestions = questions; // backup original questions
            $scope.questions = questions;
        });
        /**/



        /**
         * Initialization of JMP questionnaire
         * Uncomment next lines
         *
         // @todo improve me! Hardcoded value... (Urban, Rural, Total)
         requiredNumberOfAnswers = 3;
         Restangular.one('questionnaire', $routeParams.id).all('question').getList({fields: 'filter,answers'}).then(function(questions) {
             console.info(questions);
            $scope.questions = questions;
            // Store copy of original object
            angular.forEach(questions, function(question) {

                // Make sure we have the right number existing in the Model
                numberOfAnswers = question.answers.length;
                if (numberOfAnswers < requiredNumberOfAnswers) {

                    // create an empty answer for the need of NgGrid
                    for (var index = 0; index < requiredNumberOfAnswers - numberOfAnswers; index++) {
                        question.answers.push({});
                    }
                }
                $scope.originalQuestions.push(Restangular.copy(question));
            });
        });
        /**/






    }




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
            $('.icon-loading', row.elm).toggle();
        };

        // Get the field and check whether it has an error class
        if (!$('.col' + column.index, row.elm).find('input').hasClass('error')) {

            $('.col' + column.index, row.elm).css('backgroundColor', 'inherit');

            // GUI display a loading icon
            $('.icon-loading', row.elm).toggle();

            // True means the answer exists and must be updated. Otherwise, create a new answer
            if (answer.id > 0) {
                answer.$update({id: answer.id}, reloadQuestion);
            } else {
                // Convention:
                // the answerIndex == part
                // part with id 0 == the total part
                if (answerIndex > 0) {
                    answer.part = answerIndex;
                }
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
    $scope.gridOptions = {
        data: 'questions',
        enableCellSelection: true,
        showFooter: false,
        selectedItems: $scope.selectedRow,
        multiSelect: false,
        columnDefs: [
            {field: 'name', displayName: 'Question', width: '500px'},
            {field: 'answers.1.valuePercent', displayName: 'Urban', enableCellEdit: true, cellFilter: 'percent', editableCellTemplate: cellEditableTemplate}, //, cellTemplate: 'cellTemplate.html'
            {field: 'answers.2.valuePercent', displayName: 'Rural', enableCellEdit: true, cellFilter: 'percent', editableCellTemplate: cellEditableTemplate},
            {field: 'answers.0.valuePercent', displayName: 'Total', enableCellEdit: true, cellFilter: 'percent', editableCellTemplate: cellEditableTemplate},
            {displayName: '', cellTemplate: '<i class="icon-loading" style="display: none" />', cellClass: 'column-loading', width: '28px'}
        ]
    };

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

});
