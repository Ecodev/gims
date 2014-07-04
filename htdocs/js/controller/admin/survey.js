/* Controllers */
angular.module('myApp').controller('Admin/Survey/CrudCtrl', function($scope, $routeParams, $location, Modal, Restangular) {
    "use strict";

    $scope.sending = false;

    // Default redirect
    var returnUrl = '/admin/survey';
    if ($routeParams.returnUrl) {
        returnUrl = $routeParams.returnUrl;
    }

    $scope.actives = [
        {text: 'Yes', value: true},
        {text: 'No', value: false}
    ];

    $scope.saveAndClose = function() {
        this.save(returnUrl);
    };

    $scope.cancel = function() {
        $location.url(returnUrl);
    };

    $scope.save = function(redirectTo) {

        $scope.sending = true;

        // First case is for update a survey, second is for creating
        if ($scope.survey.id) {
            $scope.survey.put().then(function() {
                $scope.sending = false;

                if (redirectTo) {
                    $location.path(redirectTo);
                }
            }, function() {
                $scope.sending = false;
            });
        } else {
            Restangular.all('survey').post($scope.survey).then(function(survey) {
                $scope.sending = false;

                if (!redirectTo) {
                    redirectTo = '/admin/survey/edit/' + survey.id;
                }
                $location.path(redirectTo);
            }, function() {
                $scope.sending = false;
            });
        }
    };

    // Delete a survey
    $scope.delete = function() {
        Modal.confirmDelete($scope.survey, {label: $scope.survey.code, returnUrl: '/admin/survey'});
    };

    // Load survey if possible
    if ($routeParams.id) {
        Restangular.one('survey', $routeParams.id).get({fields: 'metadata,questionnaires,questionnaires.completed'}).then(function(survey) {
            $scope.survey = survey;
        });

    } else {
        $scope.survey = {};
    }

    $scope.tabs = [false, false, false, false];
    $scope.selectTab = function(tab) {
        $scope.tabs[tab] = true;
        $location.hash(tab);
    };

    // Set the tab from URL hash if any
    $scope.selectTab(parseInt($location.hash()));
});

/**
 * Admin Survey Controller
 */
angular.module('myApp').controller('Admin/SurveyCtrl', function($scope) {
    "use strict";

    // Configure gims-grid.
    $scope.gridOptions = {
        columnDefs: [
            {field: 'code', displayName: 'Code', width: '150px'},
            {field: 'name', displayName: 'Name'},
            {field: 'isActive', displayName: 'Active', cellFilter: 'checkmark', width: '100px'},
            {field: 'year', displayName: 'Year', width: '100px'},
            {displayName: '', width: '70px', cellTemplate: '<a class="btn btn-default btn-xs" href="/admin/survey/edit/{{row.entity.id}}"><i class="fa fa-pencil fa-lg"></i></a><button type="button" class="btn btn-default btn-xs" ng-click="remove(row)" ><i class="fa fa-trash-o fa-lg"></i></button>'}
        ]
    };
});
