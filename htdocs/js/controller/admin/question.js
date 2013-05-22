/* Controllers */
angular.module('myApp').controller('Admin/Question/CrudCtrl', function ($scope, $routeParams, $location, Restangular, Modal, Select2Configurator) {
    "use strict";

    $scope.sending = false;

    // Default redirect
    var returnUrl = '/';
    var returnTab = '';

    if ($routeParams.returnUrl) {
        returnUrl = $routeParams.returnUrl;
        returnTab = $routeParams.returnTab;
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
                $scope.question.put().then(function() {
                $scope.sending = false;

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
        Restangular.one('question', $routeParams.id).get({fields: 'metadata'}).then(function(question) {
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

    Select2Configurator.configure($scope, 'filter');
});

