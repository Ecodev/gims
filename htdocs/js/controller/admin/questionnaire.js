/* Controllers */
angular.module('myApp').controller('Admin/Questionnaire/CrudCtrl', function ($scope, $routeParams, $location, Restangular, Modal, Select2Configurator) {
    "use strict";

    $scope.sending = false;

    // Default redirect
    var returnUrl = '/';
    var returnTab = '';

    if ($routeParams.returnUrl) {
        returnUrl = $routeParams.returnUrl;
        returnTab = $routeParams.returnTab;
    }

    var redirect = function () {
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

        // First case is for update a questionnaire, second is for creating
        $scope.questionnaire.geoname = $scope.questionnaire.geoname.id;
        if ($scope.questionnaire.id) {
                $scope.questionnaire.put().then(function() {
                $scope.sending = false;

                if (redirectAfterSave) {
                    redirect();
                }
            });
        } else {
            $scope.questionnaire.survey = $routeParams.survey;
            delete $scope.questionnaire.sorting; // let the server define the sorting value
            Restangular.all('questionnaire').post($scope.questionnaire).then(function(questionnaire) {
                $scope.sending = false;

                if (redirectAfterSave) {
                    redirect();
                } else {
                    // redirect to edit URL
                    $location.path(sprintf('admin/questionnaire/edit/%s', questionnaire.id));

                }
            });
        }
    };

    // Delete a questionnaire
    $scope.delete = function () {
        Modal.confirmDelete($scope.questionnaire, {label: $scope.questionnaire.name, returnUrl: $location.search().returnUrl});
    };

    // Create object with default value
    $scope.questionnaire = {sorting: 0, type: 'foo'};

    // Try loading questionnaire if possible...
    if ($routeParams.id) {
        Restangular.one('questionnaire', $routeParams.id).get({fields: 'metadata'}).then(function(questionnaire) {
            $scope.questionnaire = questionnaire;
        });
    }

    // Load survey if possible
    var params = $location.search();
    if (params.survey !== undefined) {
        Restangular.one('survey', params.survey).get().then(function (survey) {
            $scope.survey = survey;
        });
    }

    Select2Configurator.configure($scope, 'geoname');
});

