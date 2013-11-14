/* Controllers */
angular.module('myApp').controller('Admin/Survey/CrudCtrl', function ($scope, $routeParams, $location, $resource, Modal, Restangular) {
    "use strict";

    $scope.sending = false;

    // Default redirect
    var redirectTo = '/admin/survey';
    if ($routeParams.returnUrl) {
        redirectTo = $routeParams.returnUrl;
    }

    $scope.actives = [
        {text: 'Yes', value: true},
        {text: 'No', value: false}
    ];

    $scope.saveAndClose = function () {
        this.save(redirectTo);
    };

    $scope.cancel = function () {
        $location.path(redirectTo).search('returnUrl', null).hash(null);
    };

    $scope.save = function (redirectTo) {

        $scope.sending = true;

        // First case is for update a survey, second is for creating
        if ($scope.survey.id) {
            $scope.survey.put().then(function () {
                $scope.sending = false;

                if (redirectTo) {
                    $location.path(redirectTo);
                }
            });
        } else {
            Restangular.all('survey').post($scope.survey).then(function(survey) {
                $scope.sending = false;

                if (!redirectTo) {
                    redirectTo = '/admin/survey/edit/' + survey.id;
                }
                $location.path(redirectTo);
            });
        }
    };

    // Delete a survey
    $scope.delete = function () {
        Modal.confirmDelete($scope.survey, {label: $scope.survey.code, returnUrl: '/admin/survey'});
    };

    // Load survey if possible
    if ($routeParams.id) {
        Restangular.one('survey', $routeParams.id).get({fields: 'metadata,questionnaires,questionnaires.completed'}).then(function(survey) {
            $scope.survey = survey;
        });


        Restangular.one('survey', $routeParams.id).all('question').getList({fields:'type,chapter'}).then(function(questions) {
            $scope.questions = questions;
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
angular.module('myApp').controller('Admin/SurveyCtrl', function ($scope, $location, Modal, Restangular) {
    "use strict";

    // Initialize
    $scope.surveys = Restangular.all('survey').getList();

    // Keep track of the selected row.
    $scope.selectedRow = [];

    // Configure ng-grid.
    $scope.gridOptions = {
        plugins: [new ngGridFlexibleHeightPlugin({minHeight: 800})],
        data: 'surveys',
        enableCellSelection: true,
        showFooter: false,
        selectedItems: $scope.selectedRow,
        filterOptions: {},
        multiSelect: false,
        columnDefs: [
            {field: 'code', displayName: 'Code', width: '150px'},
            {field: 'name', displayName: 'Name', width: '750px'},
            {field: 'isActive', displayName: 'Active', cellFilter: 'checkmark', width: '100px'},
            {field: 'year', displayName: 'Year', width: '100px'},
            {displayName: '', cellTemplate: '<a class="btn btn-default btn-xs" href="/admin/survey/edit/{{row.entity.id}}"><i class="icon-pencil icon-large"></i></a>' +
                        '<button type="button" class="btn btn-default btn-xs" ng-click="remove(row)" ><i class="icon-trash icon-large"></i></button>'}
        ]
    };

    $scope.remove = function (row) {
        Modal.confirmDelete(row.entity, {objects: $scope.surveys, label: row.entity.code, returnUrl: '/admin/survey'});
    };

});