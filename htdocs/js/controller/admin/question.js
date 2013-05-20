/* Controllers */
angular.module('myApp').controller('Admin/Question/CrudCtrl', function ($scope, $routeParams, $location, Restangular, Modal, Select2Configurator) {
    "use strict";

    $scope.sending = false;

    // Default redirect
    var redirectTo = '/';

    if ($routeParams.returnUrl) {
        redirectTo = $routeParams.returnUrl;
    }

    $scope.cancel = function () {
        $location.path(redirectTo).search({});
    };

    $scope.saveAndClose = function () {
        this.save(redirectTo);
    };

    $scope.save = function (redirectTo) {

        $scope.sending = true;

        // First case is for update a question, second is for creating
        $scope.question.filter = $scope.question.filter.id;
        if ($scope.question.id) {
                $scope.question.put().then(function() {
                $scope.sending = false;

                if (redirectTo) {
                    $location.path(redirectTo).search({});
                }
            });
        } else {
            $scope.question.survey = $routeParams.survey;
            delete $scope.question.sorting; // let the server define the sorting value
            Restangular.all('question').post($scope.question).then(function(question) {
                $scope.sending = false;

                if (redirectTo) {
                    $location.path(redirectTo).search({});
                } else {
                    // redirect to edit URL
                    redirectTo = sprintf('admin/question/edit/%s', question.id);
                    $location.path(redirectTo);

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

    Select2Configurator.configure($scope, 'filter');
});

