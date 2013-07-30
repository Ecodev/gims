/* Controllers */
angular.module('myApp').controller('Admin/Question/CrudCtrl', function ($scope, $routeParams, $location, Restangular, Modal) {
    "use strict";


    $scope.types = [
        {text: 'Info', value: 'info'},
        {text: 'Numerical (3 answers)', value: 'numerical'},
        {text: 'Numerical (4 answers)', value: 'numerical'},
        {text: 'Numerical (5 answers)', value: 'numerical'},
        {text: 'Percentage', value: 'foo'}
    ];


    $scope.sending = false;

    // Default redirect
    var returnUrl = '/';
    var returnTab = '';

    if ($routeParams.returnUrl) {
        returnUrl = $routeParams.returnUrl;
        returnTab = $routeParams.returnTab;
        $('.survey-question-link').attr('href', returnUrl + '#' + returnTab);
    }

    var redirect = function() {
        $location.path(returnUrl).search({}).hash(returnTab);
    };

    $scope.cancel = function () {
        redirect();
    };

    $scope.saveAndClose = function () {
        this.save(true);
    };

    $scope.save = function (redirectAfterSave) {

        $scope.sending = true;


        // First case is for update a question, second is for creating
        $scope.question.filter = $scope.question.filter.id;
        if ($scope.question.id) {
                $scope.question.put({fields: 'metadata,filter,survey'}).then(function(question) {
                $scope.sending = false;
                $scope.question= question;

                if (redirectAfterSave) {
                    redirect();
                }
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
            });
        }
    };

    // Delete a question
    $scope.delete = function () {
        Modal.confirmDelete($scope.question, {label: $scope.question.name, returnUrl: $location.search().returnUrl});
    };

    // Create object with default value
    $scope.question = {sorting: 0, type: 'foo'};

    // Try loading question if possible...
    if ($routeParams.id) {
        Restangular.one('question', $routeParams.id).get({fields: 'metadata,filter,survey'}).then(function(question) {

            $scope.question = question;
        });
    }

    // Load survey if possible
    var params = $location.search();
    if (params.survey !== undefined) {
        Restangular.one('survey', params.survey).get().then(function (survey) {
            $scope.survey = survey;
        });
    }
});

