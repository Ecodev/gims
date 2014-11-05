/* Controllers */
angular.module('myApp').controller('Admin/Questionnaire/CrudCtrl', function($scope, $routeParams, $location, Restangular, Modal) {
    "use strict";

    var allStatus = [
        {
            text: 'New',
            value: 'new'
        },
        {
            text: 'Completed',
            value: 'completed'
        },
        {
            text: 'Validated',
            value: 'validated'
        },
        {
            text: 'Published',
            value: 'published'
        },
        {
            text: 'Rejected',
            value: 'rejected'
        }
    ];

    $scope.status = allStatus;

    $scope.sending = false;

    // Default redirect
    var returnUrl = '/';

    if ($routeParams.returnUrl) {
        returnUrl = $routeParams.returnUrl;
    }

    var redirect = function() {
        $location.url(returnUrl);
    };

    $scope.cancel = function() {
        redirect();
    };

    $scope.saveAndClose = function() {
        this.save(true);
    };

    $scope.save = function(redirectAfterSave) {

        $scope.sending = true;

        // First case is for update a questionnaire, second is for creating
        var geoname = $scope.questionnaire.geoname;
        $scope.questionnaire.geoname = $scope.questionnaire.geoname.id;
        if ($scope.questionnaire.id) {
            $scope.questionnaire.put().then(function() {
                $scope.sending = false;

                if (redirectAfterSave) {
                    redirect();
                }
            }, function() {
                $scope.sending = false;
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
            }, function() {
                $scope.sending = false;
            });
        }
        $scope.questionnaire.geoname = geoname;
    };

    // Delete a questionnaire
    $scope.delete = function() {
        Modal.confirmDelete($scope.questionnaire, {
            label: $scope.questionnaire.name,
            returnUrl: '/admin/survey/edit/' + $location.search().survey
        });
    };

    // Create object with default value
    $scope.statusDisabled = false;
    $scope.questionnaire = {};

    // Try loading questionnaire if possible...
    if ($routeParams.id) {
        Restangular.one('questionnaire', $routeParams.id).get({fields: 'metadata,geoname,status,dateObservationStart,dateObservationEnd,comments,name,permissions,survey'}).then(function(questionnaire) {
            $scope.questionnaire = questionnaire;
            $scope.survey = questionnaire.survey;
        });
    }

    // Only show 'validated' option if it is already validated, or if the user has enough permission to select it
    $scope.$watch('questionnaire', function(questionnaire) {
        $scope.status = _.filter(allStatus, function(status) {
            if (status.value == 'validated') {
                return questionnaire.status == 'validated' || (questionnaire.permissions && questionnaire.permissions.validate);
            } else if (status.value == 'published') {
                return questionnaire.status == 'published' || (questionnaire.permissions && questionnaire.permissions.publish);
            }

            return true;
        });
    });

    // Load survey if possible
    var params = $location.search();
    if (params.survey !== undefined) {
        Restangular.one('survey', params.survey).get().then(function(survey) {
            $scope.survey = survey;
        });
    }

    $scope.tabs = [false, false, false, false];
    $scope.selectTab = function(tab) {
        $scope.tabs[tab] = true;
        $location.hash(tab);
    };

    // Set the tab from URL hash if any
    $scope.selectTab(parseInt($location.hash()));
});
