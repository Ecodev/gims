/* Controllers */
angular.module('myApp').controller('Admin/Questionnaire/CrudCtrl', function ($scope, $routeParams, $location, Restangular, Modal, Select2Configurator) {
    "use strict";

    $scope.status = [
        {text: 'New', value: 'new'}
    ];

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
            $scope.questionnaire.status = 'new';
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
    $scope.statusDisabled = false;
    $scope.questionnaire = {permission: null};

    // Try loading questionnaire if possible...
    if ($routeParams.id) {
        Restangular.one('questionnaire', $routeParams.id).get({fields: 'metadata,geoname'}).then(function(questionnaire) {
            $scope.questionnaire = questionnaire;
        });
    }

    // @todo fetch user "me" for having current capability
//    Restangular.one('me').then(function (user) {
//        $scope.user = user;
//    });

    // When questionnaire changes, navigate to its URL
    $scope.$watch('questionnaire', function (questionnaire) {
        if (questionnaire.permission !== undefined) {

            // @todo set select to disable if status is unreachable by the user.
            // $scope.statusDisabled = false;
            if (questionnaire.permission.canBeCompleted) {
                $scope.status.push({text: 'Completed', value: 'completed'});
            }

            if (questionnaire.permission.canBeValidated) {
                $scope.status.push({text: 'Validated', value: 'validated'});
            }
        }
    });

    // Load survey if possible
    var params = $location.search();
    if (params.survey !== undefined) {
        Restangular.one('survey', params.survey).get().then(function (survey) {
            $scope.survey = survey;
        });
    }

    Select2Configurator.configure($scope, 'geoname');
});

