/* Controllers */
angular.module('myApp').controller('Admin/Survey/CrudCtrl', function ($scope, $routeParams, $location, $resource, Modal, Restangular) {
    "use strict";

    $scope.sending = false;

    // Default redirect
    var redirectTo = '/admin/survey';
    if ($routeParams.returnUrl) {
        redirectTo = $routeParams.returnUrl;
    }

    $scope.cancel = function () {
        $location.path(redirectTo).search('returnUrl', null);
    };

    $scope.actives = [
        {text: 'Yes', value: 'true'},
        {text: 'No', value: 'false'}
    ];

    $scope.saveAndClose = function () {
        this.save(redirectTo);
    };

    $scope.cancel = function () {
        $location.path(redirectTo).search('returnUrl', null);
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
        Restangular.one('survey', $routeParams.id).get({fields: 'metadata'}).then(function(survey) {

            // Cast "active" to be string for the need of the select menu.
            survey.active += ''; // string value
            $scope.survey = survey;
        });
    } else {
        $scope.survey = {};
    }

    // initialize the panes model with hardcoded value
    $scope.panes = [{},{},{}];
    $scope.panes[2].active = true;

    $scope.edit = function (row) {
        var currentUrl = $location.path();
        $location.path('/admin/question/edit/' + row.entity.id).search({'returnUrl': currentUrl});
    };

});

/**
 * Admin Survey Controller
 */
angular.module('myApp').controller('Admin/SurveyCtrl', function ($scope, $location, Modal, Restangular) {
    "use strict";

    // Initialize
    $scope.filteringText = '';
    $scope.filterOptions = {
        filterText: 'filteringText',
        useExternalFilter: false
    };

    $scope.surveys = Restangular.all('survey').getList();

    // Keep track of the selected row.
    $scope.selectedRow = [];

    // Configure ng-grid.
    $scope.gridOptions = {
        plugins: [new ngGridFlexibleHeightPlugin({minHeight: 100})],
        data: 'surveys',
        enableCellSelection: true,
        showFooter: true,
        selectedItems: $scope.selectedRow,
        filterOptions: $scope.filterOptions,
        multiSelect: false,
        columnDefs: [
            {field: 'code', displayName: 'Code', width: '150px'},
            {field: 'name', displayName: 'Name', width: '750px'},
            {field: 'active', displayName: 'Active', cellFilter: 'checkmark', width: '100px'},
            {field: 'year', displayName: 'Year', width: '100px'},
            {displayName: '', cellTemplate: '<button type="button" class="btn btn-mini" ng-click="edit(row)" ><i class="icon-pencil icon-large"></i></button>' +
                '<button type="button" class="btn btn-mini" ng-click="remove(row)" ><i class="icon-trash icon-large"></i></button>'}
        ]
    };

    $scope.remove = function (row) {
        Modal.confirmDelete(row.entity, {objects: $scope.surveys, label: row.entity.code, returnUrl: '/admin/survey'});
    };

    $scope.edit = function (row) {
        $location.path('/admin/survey/edit/' + row.entity.id);
    };

    $scope.$on('filterChanged', function (evt, text) {
        $scope.filteringText = text;
    });
});