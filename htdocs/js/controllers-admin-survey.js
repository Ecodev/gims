/* Controllers */
angular.module('myApp').controller('Admin/Survey/CrudCtrl', function ($scope, $routeParams, $location, $window, $timeout, $resource, Survey, Modal, Gui) {
    "use strict";

    Gui.resetSaveButton($scope);

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
        if ($scope.survey.id > 0) {
            $scope.survey.$update({id: $scope.survey.id}, function () {
                Gui.resetSaveButton($scope);

                if (redirectTo) {
                    $location.path(redirectTo);
                }
            });
        } else {
            $scope.survey = new Survey($scope.survey);
            $scope.survey.$create(function () {

                Gui.resetSaveButton($scope);

                if (redirectTo) {
                    $location.path(redirectTo);
                }
            });
        }
    };

    // Delete a survey
    $scope.remove = function () {
        Modal.confirmDelete($scope.survey);
    };

    // Load survey if possible
    if ($routeParams.id > 0) {
        Survey.get({id: $routeParams.id}, function (survey) {

            // Cast "active" to be string for the need of the select menu.
            survey.active += ''; // string value
            $scope.survey = new Survey(survey);

            // Trigger resize event informing elements to resize according to the height of the window.
            $timeout(function () {
                angular.element($window).resize();
            }, 0);
        });
    }

    // initialize the panes model with hardcoded value
    $scope.panes = [{},{},{}];
    $scope.panes[1].active = true;

    $scope.edit = function (row) {
        var currentUrl = $location.path();
        $location.path('/admin/question/edit/' + row.entity.id).search({'returnUrl': currentUrl});
    };

    // Keep track of the selected row.
    $scope.selectedRow = [];

    // Initialize
    $scope.filteringText = '';
    $scope.filterOptions = {
        filterText: 'filteringText',
        useExternalFilter: false
    };

    // Configure ng-grid.
    $scope.gridQuestions = {
        data: 'survey.questions',
        enableCellSelection: true,
        showFooter: true,
        selectedItems: $scope.selectedRow,
        filterOptions: $scope.filterOptions,
        multiSelect: false,
        columnDefs: [
            {field: 'sorting', displayName: '#', width: '50px'},
            {field: 'name', displayName: 'Name'},
            {displayName: '', width: '100px', cellTemplate: '<button type="button" class="btn btn-mini" ng-click="edit(row)" ><i class="icon-pencil icon-large"></i></button>' +
                '<button type="button" class="btn btn-mini" ng-click="delete(row)" ><i class="icon-trash icon-large"></i></button>'}
        ]
    };

    $scope.$on('filterChanged', function (evt, text) {
        console.log(text);
        $scope.filteringText = text;
    });
});

/**
 * Admin Survey Controller
 */
angular.module('myApp').controller('Admin/SurveyCtrl', function ($scope, $routeParams, $location, $window, $timeout, $resource, Survey, Modal) {
    "use strict";

    // Initialize
    $scope.filteringText = '';
    $scope.filterOptions = {
        filterText: 'filteringText',
        useExternalFilter: false
    };

    $scope.surveys = $resource('/api/survey?fields=metadata,comments,questions').query(function () {

        // Trigger resize event informing elements to resize according to the height of the window.
        $timeout(function () {
            angular.element($window).resize();
        }, 0);
    });

    // Keep track of the selected row.
    $scope.selectedRow = [];

    // Configure ng-grid.
    $scope.gridOptions = {
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
                '<button type="button" class="btn btn-mini" ng-click="delete(row)" ><i class="icon-trash icon-large"></i></button>'}
        ]
    };

    $scope.remove = function () {

        // Add a little timeout to enabling the event "selectRow" to be propagated
        $timeout(function () {
            Modal.confirmDelete($scope.selectedRow[0], $scope.surveys);
        }, 0);
    };

    $scope.edit = function (row) {
        $location.path('/admin/survey/edit/' + row.entity.id);
    };

    $scope.$on('filterChanged', function (evt, text) {
        $scope.filteringText = text;
    });
});