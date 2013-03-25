'use strict';

/* Controllers */


angular.module('myApp').controller('MyCtrl1', function() {

});

angular.module('myApp').controller('MyCtrl2', function() {

});

angular.module('myApp').controller('UserCtrl', function($scope, $location) {

    $scope.promptLogin = function() {
        $scope.showLogin = true;
        $scope.redirect = $location.absUrl();
    };

    $scope.cancelLogin = function() {
        $scope.showLogin = false;
    };

    $scope.promptRegister = function() {
        $scope.showRegister = true;
        $scope.redirect = $location.absUrl();
    };

    $scope.cancelRegister = function() {
        $scope.showRegister = false;
    };

    $scope.opts = {
        backdropFade: true,
        dialogFade: true
    };

    // Keep current URL up to date, so we can login and come back to current page
    $scope.redirect = $location.absUrl();
    $scope.$on("$routeChangeSuccess", function(event, current, previous) {
        $scope.redirect = $location.absUrl();
    });
});

angular.module('myApp').controller('QuestionnaireCtrl', function($scope, $resource, $routeParams, $location, answerService, questionnaireService) {

    // show case which update Answer id: 41
    var answer = answerService.get({id: 41}, function () {
        answer.valuePercent = 0.23;
        answer.question.name = 'asdf';
        answer.$update({id: answer.id});
    });

    // If a questionnaire is specified in URL, load its data
    $scope.answers = [];
    if ($routeParams.id)
    {
        $scope.answers = answerService.query({idQuestionnaire: $routeParams.id});

        // Here we use synchronous style affectation to be able to set initial
        // value of Select2 (after Select2 itself is initialized)
        questionnaireService.get({id: $routeParams.id}, function(questionnaire) {
            $scope.selectedQuestionnaire = questionnaire;
        });
    }

    // When questionnaire changes, navigate to its URL
    $scope.$watch('selectedQuestionnaire', function(questionnaire) {
        if (questionnaire && (questionnaire.id != $routeParams.id)) {
            $location.path('/contribute/questionnaire/' + questionnaire.id);
        }
    });

    // Configure ng-grid
    $scope.gridOptions = {
        data: 'answers',
        enableCellSelection: true,
        showFooter: true,
        columnDefs: [
            {field: 'id', displayName: 'Id'},
            {field: 'question.name', displayName: 'Name'},
            {field: 'question.category.name', displayName: 'Category'},
            {field: 'valuePercent', displayName: 'Answer', enableCellEdit: true}
        ]
    };

    var formatSelection = function(questionnaire) {
        return  questionnaire.id + ' ' +  questionnaire.survey.name + " - (" + questionnaire.survey.code + ")";
    };
    var formatResult = function(questionnaire) {
        return formatSelection(questionnaire);
    };

    var questionnaires;
    questionnaireService.query(function(data) {
        questionnaires = data;
    });

    $scope.availableQuestionnaires = {
        query: function(query) {
            var data = {results: []};

            var searchTerm = query.term.toUpperCase();
            var regexp = new RegExp(searchTerm);

            angular.forEach(questionnaires, function(questionnaire) {
                var blob = (questionnaire.id + ' ' + questionnaire.survey.name + ' ' + questionnaire.survey.code).toUpperCase();
                if (regexp.test(blob))
                {
                    data.results.push(questionnaire);
                }
            });
            query.callback(data);
        },
        formatResult: formatResult,
        formatSelection: formatSelection
    };

});
