'use strict';

/* Controllers */
angular.module('myApp').controller('Admin/User/CrudCtrl', function($scope, $routeParams, $location, $resource, User, UserSurvey, UserQuestionnaire, Survey, Role, Modal, Gui, Select2Configurator) {

    Select2Configurator.configure($scope, Survey, 'survey');
    Select2Configurator.configure($scope, Role, 'role');
    // Default redirect
    var redirectTo = '/admin/user';
    if ($routeParams.returnUrl) {
        redirectTo = $routeParams.returnUrl;
    }

    $scope.cancel = function() {
        $location.path(redirectTo).search('returnUrl', null);
    };

    // Configure ng-grid.
    $scope.userSurvey = $routeParams.id ? UserSurvey.query({idUser: $routeParams.id}) : [];
    $scope.gridSurveyOptions = {
        plugins: [new ngGridFlexibleHeightPlugin({minHeight: 100})],
        data: 'userSurvey',
        filterOptions: {
            filterText: 'filteringText',
            useExternalFilter: false
        },
        multiSelect: false,
        columnDefs: [
            {field: 'survey.name', displayName: 'Survey'},
            {field: 'role.name', displayName: 'Role', width: '250px'},
            {cellTemplate: '<button type="button" class="btn btn-mini" ng-click="deleteUserSurvey(row)"><i class="icon-trash icon-large"></i></button>'}
        ]
    };
    $scope.addUserSurvey = function() {
        var userSurvey = new UserSurvey({
            user: $routeParams.id,
            survey: $scope.select2.survey.selected.id,
            role: $scope.select2.role.selected.id
        });
        $scope.isLoading = true;
        userSurvey.$create({idUser: userSurvey.user}, function(userSurvey) {
            $scope.userSurvey.push(userSurvey);
            $scope.isLoading = false;
        });
    };
    // Configure ng-grid.
    $scope.userQuestionnaire = $routeParams.id ? UserQuestionnaire.query({idUser: $routeParams.id}) : [];
    $scope.gridQuestionnaireOptions = {
        data: 'userQuestionnaire',
        plugins: [new ngGridFlexibleHeightPlugin({minHeight: 100})],
        filterOptions: {
            filterText: 'filteringText',
            useExternalFilter: false
        },
        multiSelect: false,
        columnDefs: [
            {field: 'questionnaire.name', displayName: 'Questionnaire'},
            {field: 'role.name', displayName: 'Role', width: '250px'},
            {cellTemplate: '<button type="button" class="btn btn-mini" ng-click="delete(row)"><i class="icon-trash icon-large"></i></button>'}
        ]
    };

    $scope.actives = [
        {text: 'Yes', value: 'true'},
        {text: 'No', value: 'false'}
    ];

    $scope.saveAndClose = function() {
        this.save(redirectTo);
    };

    $scope.save = function(redirectTo) {
        $scope.sending = true;

        // First case is for update a user, second is for creating
        if ($scope.user.id) {
            $scope.user.$update({id: $scope.user.id}, function(user) {
                Gui.resetSaveButton($scope);

                if (redirectTo) {
                    $location.path(redirectTo);
                }
            });
        } else {
            $scope.user.$create(function(user) {
                Gui.resetSaveButton($scope);

                if (!redirectTo) {
                    redirectTo = '/admin/user/edit/' + user.id;
                }
                $location.path(redirectTo);
            });
        }
    };

    // Delete a user
    $scope.delete = function() {
        Modal.confirmDelete($scope.user, {label: $scope.user.name, returnUrl: '/admin/user'});
    };

    // Delete a userSurvey
    $scope.deleteUserSurvey = function(row) {
        Modal.confirmDelete(row.entity, {params: {idUser: $scope.user.id}, objects: $scope.userSurvey, label: row.entity.survey.name + ' - ' + row.entity.role.name});
    };

    // Load user if possible
    if ($routeParams.id > 0) {
        User.get({id: $routeParams.id, fields: 'metadata'}, function(user) {

            $scope.user = new User(user);
        });
    } else {
        $scope.user = new User();
    }
});

/**
 * Admin User Controller
 */
angular.module('myApp').controller('Admin/UserCtrl', function($scope, $location, User, Modal) {

    // Initialize
    $scope.filteringText = '';

    $scope.filterOptions = {
        filterText: 'filteringText',
        useExternalFilter: false
    };

    $scope.users = User.query();

    // Keep track of the selected row.
    $scope.selectedRow = [];

    // Configure ng-grid.
    $scope.gridOptions = {
        data: 'users',
        plugins: [new ngGridFlexibleHeightPlugin({minHeight: 100})],
        enableCellSelection: true,
        showFooter: true,
        selectedItems: $scope.selectedRow,
        filterOptions: $scope.filterOptions,
        multiSelect: false,
        columnDefs: [
            {field: 'name', displayName: 'Name', width: '250px'},
            {field: 'email', displayName: 'Email', cellTemplate: '<div class="ngCellText" ng-class="col.colIndex()"><a href="mailto:{{row.entity[col.field]}}">{{row.entity[col.field]}}</a></div>'},
            {field: 'active', displayName: 'Active', cellFilter: 'checkmark', width: '100px'},
            {displayName: '', cellTemplate: '<button type="button" class="btn btn-mini" ng-click="edit(row)" ><i class="icon-pencil icon-large"></i></button>' +
                        '<button type="button" class="btn btn-mini" ng-click="delete(row)" ><i class="icon-trash icon-large"></i></button>'}
        ]
    };

    $scope.delete = function(row) {
        Modal.confirmDelete(row.entity, {objects: $scope.users, label: row.entity.name});
    };

    $scope.edit = function(row) {
        $location.path('/admin/user/edit/' + row.entity.id);
    };

    $scope.$on('filterChanged', function(evt, text) {
        $scope.filteringText = text;
    });
});