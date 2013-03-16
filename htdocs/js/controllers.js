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


angular.module('myApp').controller('QuestionnaireCtrl', function($scope, $resource, $routeParams, $location) {

    var Question = $resource('/api/questionnaire/:idQuestionnaire/question');
    var Questionnaire = $resource('/api/questionnaire/:id');

    // If a questionnaire is specified in URL, load its data
    $scope.questions = [];
    if ($routeParams.id)
    {
        $scope.questions = Question.query({idQuestionnaire: $routeParams.id});

        // Here we use synchronous style affectation to be able to set initial 
        // value of Select2 (after Select2 itself is initialized)
        Questionnaire.get({id: $routeParams.id}, function(questionnaire) {
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
        data: 'questions',
        columnDefs: [
            {field: 'id', displayName: 'Id'},
            {field: 'name', displayName: 'Name'},
            {field: 'category.name', displayName: 'Category'},
            {field: 'survey.name', displayName: 'Answer'}
        ]
    };

    var formatSelection = function(questionnaire) {
        return  questionnaire.id + ' ' +  questionnaire.survey.name + " - (" + questionnaire.survey.code + ")";
    };
    var formatResult = function(questionnaire) {
        return formatSelection(questionnaire);
    };

    var questionnaires;
    Questionnaire.query(function(data) {
        questionnaires = data;
    });

    $scope.currencies = {
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
