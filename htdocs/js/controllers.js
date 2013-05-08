'use strict';

/* Controllers */


angular.module('myApp').controller('MyCtrl1', function () {

});

angular.module('myApp').controller('MyCtrl2', function () {

});

angular.module('myApp').controller('UserCtrl', function ($scope, $location) {

    $scope.promptLogin = function () {
        $scope.showLogin = true;
        $scope.redirect = $location.absUrl();
    };

    $scope.cancelLogin = function () {
        $scope.showLogin = false;
    };

    $scope.promptRegister = function () {
        $scope.showRegister = true;
        $scope.redirect = $location.absUrl();
    };

    $scope.cancelRegister = function () {
        $scope.showRegister = false;
    };

    $scope.opts = {
        backdropFade: true,
        dialogFade: true
    };

    // Keep current URL up to date, so we can login and come back to current page
    $scope.redirect = $location.absUrl();
    $scope.$on("$routeChangeSuccess", function (event, current, previous) {
        $scope.redirect = $location.absUrl();
    });
});

angular.module('myApp').controller('Contribute/QuestionnaireCtrl', function ($scope, $routeParams, $location, $timeout, $window, Question, Questionnaire, Answer) {

    var cellEditableTemplate, numberOfAnswers, requiredNumberOfAnswers;

    $scope.questions = [];
    $scope.originalQuestions = []; // store original questions

    // If a questionnaire is specified in URL, load its data
    if ($routeParams.id) {

        // @todo improve me! Hardcoded value... (Urban, Rural, Total)
        requiredNumberOfAnswers = 3;
        $scope.questions = Question.query({idQuestionnaire: $routeParams.id}, function (questions) {

            // Store copy of original object
            angular.forEach($scope.questions, function (question) {

                // Make sure we have the right number existing in the Model
                numberOfAnswers = question.answers.length;
                if (numberOfAnswers < requiredNumberOfAnswers) {

                    // create an empty answer for the need of NgGrid
                    for (var index = 0; index < requiredNumberOfAnswers - numberOfAnswers; index++) {
                        question.answers.push(new Answer());
                    }
                }
                $scope.originalQuestions.push(new Question(question));
            });

            // Trigger resize event informing elements to resize according to the height of the window.
            $timeout(function () {
                angular.element($window).resize();
            }, 0);
        });

        // Here we use synchronous style affectation to be able to set initial
        // value of Select2 (after Select2 itself is initialized)
        Questionnaire.get({id: $routeParams.id}, function (questionnaire) {
            $scope.selectedQuestionnaire = questionnaire;
        });
    }

    // When questionnaire changes, navigate to its URL
    $scope.$watch('selectedQuestionnaire', function (questionnaire) {
        if (questionnaire && (questionnaire.id != $routeParams.id)) {
            $location.path('/contribute/questionnaire/' + questionnaire.id);
        }
    });

    // Update Answer method
    $scope.validateAnswer = function (column, row) {

        var answerIndex = /[0-9]+/g.exec(column.field)[0];
        var answer = new Answer(row.entity.answers[answerIndex]);


        // Allowed value is between [0-1]
        if (answer.valuePercent >= 0 && answer.valuePercent <= 1) {
            $('.col' + column.index, row.elm).find('input').removeClass('error');
        } else {
            // Get the input field to wrap it with error div
            $('.col' + column.index, row.elm).find('input').addClass('error');
        }
    };

    // Update Answer method
    $scope.updateAnswer = function (column, row) {

        var reg = new RegExp('[0-9]+', "g");
        var answerIndex = reg.exec(column.field)[0];
        var question = row.entity;

        // @todo change me to take advantage of selected row (?)
        // var answer = $scope.selectedRow
        var answer = new Answer(question.answers[answerIndex]);

        // Get the field and check whether it has an error class
        if (!$('.col' + column.index, row.elm).find('input').hasClass('error')) {

            $('.col' + column.index, row.elm).css('backgroundColor', 'inherit');

            // GUI display a loading icon
            $('.icon-loading', row.elm).toggle();

            // True means the answer exists and must be updated. Otherwise, create a new answer
            if (answer.id > 0) {
                answer.$update({id: answer.id}, function (data) {

                    // Update the question model in memory. Other way?
                    question.$get({idQuestionnaire: $routeParams.id, id: question.id});

                    // GUI remove the loading icon
                    $('.icon-loading', row.elm).toggle();
                });
            } else {
                // Convention:
                // the answerIndex == part
                // part with id 0 == the total part
                if (answerIndex > 0) {
                    answer.part = answerIndex;
                }
                answer.question = question.id;
                answer.questionnaire = $routeParams.id;
                answer.$create(function (data) {

                    // Update the question model in memory. Other way?
                    question.$get({idQuestionnaire: $routeParams.id, id: question.id});

                    // GUI remove the loading icon
                    $('.icon-loading', row.elm).toggle();
                });
            }

        } else {
            $('.col' + column.index, row.elm).css('backgroundColor', '#FF6461');
        }
    };

    // Template for cell editing with input "number".
    cellEditableTemplate = '<input style="width: 90%" step="any" type="number" ng-class="\'colt\' + col.index" ng-input="COL_FIELD" ng-blur="updateAnswer(col, row)" ng-keyup="validateAnswer(col, row)">';

    // Keep track of the selected row.
    $scope.selectedRow = [];

    // Configure ng-grid.
    $scope.gridOptions = {
        data: 'questions',
        enableCellSelection: true,
        showFooter: true,
        selectedItems: $scope.selectedRow,
        multiSelect: false,
        columnDefs: [
            {field: 'filter.name', displayName: 'Filter'},
            {field: 'name', displayName: 'Name', width: '500px'},
            {field: 'answers.1.valuePercent', displayName: 'Urban', enableCellEdit: true, cellFilter: 'percent', editableCellTemplate: cellEditableTemplate}, //, cellTemplate: 'cellTemplate.html'
            {field: 'answers.2.valuePercent', displayName: 'Rural', enableCellEdit: true, cellFilter: 'percent', editableCellTemplate: cellEditableTemplate},
            {field: 'answers.0.valuePercent', displayName: 'Total', enableCellEdit: true, cellFilter: 'percent', editableCellTemplate: cellEditableTemplate},
            {displayName: '', cellTemplate: '<i class="icon-loading" style="display: none" />', cellClass: 'column-loading', width: '28px'}
        ]
    };

    // Counter of request being sent.
    $scope.sending = 0;
    $scope.sendLabel = 'Save';

    // Update Data
    $scope.updateAnswers = function () {
        angular.forEach($scope.questions, function (question, key) {
            var questionOriginal = $scope.originalQuestions[key];

            // save the question only if it is different from the original
            if (!angular.equals(question, questionOriginal)) {
                $scope.sending = $scope.sending + question.answers.length;
                $scope.sendLabel = 'Saving ' + $scope.sending + ' object(s) ...';

                // create an answer
                angular.forEach(question.answers, function (answerObject) {
                    var answer = new Answer(answerObject);
                    answer.$update({id: answer.id}, function (data) {
                        $scope.sending--;
                        $scope.sendLabel = 'Saving ' + $scope.sending + ' object(s) ...';
                        if ($scope.sending === 0) {
                            $scope.sendLabel = 'Save';
                        }
                    });
                });
            }
        });
    };

    var formatSelection = function (questionnaire) {
        return questionnaire.name;
    };

    var formatResult = function (questionnaire) {
        return formatSelection(questionnaire);
    };

    var questionnaires;
    Questionnaire.query(function (data) {
        questionnaires = data;
    });

    $scope.availableQuestionnaires = {
        query: function (query) {
            var data = {results: []};

            var searchTerm = query.term.toUpperCase();
            var regexp = new RegExp(searchTerm);

            angular.forEach(questionnaires, function (questionnaire) {
                var blob = (questionnaire.id + ' ' + questionnaire.name + ' ' + questionnaire.survey.name).toUpperCase();
                if (regexp.test(blob)) {
                    data.results.push(questionnaire);
                }
            });
            query.callback(data);
        },
        formatResult: formatResult,
        formatSelection: formatSelection
    };

});


angular.module('myApp').controller('Browse/ChartCtrl', function ($scope, $http, Country, Part, FilterSet, Select2Configurator, $timeout) {

    // Configure select2 via our helper service
    Select2Configurator.configure($scope, Country, 'country');
    Select2Configurator.configure($scope, Part, 'part');
    Select2Configurator.configure($scope, FilterSet, 'filterSet');

    // Whenever one of the parameter is changed
    var uniqueAjaxRequest;
    $scope.$watch('select2.country.selected.id + select2.part.selected.id + select2.filterSet.selected.id', function (a) {

        // If they are all available ...
        if ($scope.select2.country.selected && $scope.select2.part.selected && $scope.select2.filterSet.selected) {
            $scope.isLoading = true;
            $timeout.cancel(uniqueAjaxRequest);
            uniqueAjaxRequest = $timeout(function () {

                // ... then, get chart data via Ajax, but only once per 200 milliseconds
                // (this avoid sending several request on page loading)
                $http.get('/api/chart',
                        {
                            params: {
                                country: $scope.select2.country.selected.id,
                                part: $scope.select2.part.selected.id,
                                filterSet: $scope.select2.filterSet.selected.id
                            }
                        }).success(function (data) {
                    $scope.chart = data;
                    $scope.isLoading = false;
                });
            }, 200);
        }
    });

});