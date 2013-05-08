'use strict';

/* Controllers */
angular.module('myApp').controller('AdminCtrl', function() {

});

angular.module('myApp').controller('Admin/User/CrudCtrl', function($scope, $routeParams, $location, $resource, User, UserSurvey, UserQuestionnaire, Modal, Gui) {



    // Configure ng-grid.
    $scope.userSurvey = UserSurvey.query({idUser: $routeParams.id});
    $scope.gridSurveyOptions = {
        data: 'userSurvey',
        showFooter: true,
        filterOptions: {
            filterText: 'filteringText',
            useExternalFilter: false
        },
        multiSelect: false,
        columnDefs: [
            {field: 'survey.name', displayName: 'Survey'},
            {field: 'role.name', displayName: 'Role', width: '250px'},
            {cellTemplate: '<button type="button" class="btn btn-mini" ng-click="delete(row)"><i class="icon-trash icon-large"></i></button>'}
        ]
    };

    // Configure ng-grid.
    $scope.userQuestionnaire = UserQuestionnaire.query({idUser: $routeParams.id});
    $scope.gridQuestionnaireOptions = {
        data: 'userQuestionnaire',
        showFooter: true,
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

    Gui.resetSaveButton($scope);

    $scope.saveAndClose = function() {
        this.save('/admin/user');
    };

    $scope.save = function(routeTo) {
        $scope.sending = true;
        $scope.sendLabel = '<i class="icon-ok"></i> Saving...';

        // First case is for update a user, second is for creating
        if ($scope.user.id > 0) {
            $scope.user.$update({id: $scope.user.id}, function(user) {
                Gui.resetSaveButton($scope);

                if (routeTo) {
                    $location.path(routeTo);
                }
            });
        } else {
            $scope.user = new User($scope.user);
            $scope.user.$create(function(user) {

                Gui.resetSaveButton($scope);

                if (routeTo) {
                    $location.path(routeTo);
                }
            });
        }
    };

    // Delete a user
    $scope.delete = function() {
        Modal.confirmDelete($scope.user);
    };

    // Load user if possible
    if ($routeParams.id > 0) {
        $resource('/api/user/:id?fields=metadata').get({id: $routeParams.id}, function(user) {

            $scope.user = new User(user);
        });
    }
});

/**
 * Admin User Controller
 */
angular.module('myApp').controller('Admin/UserCtrl', function($scope, $routeParams, $location, $window, $timeout, User, Modal) {

    // Initialize
    $scope.filteringText = '';

    $scope.filterOptions = {
        filterText: 'filteringText',
        useExternalFilter: false
    };

    $scope.users = User.query(function(data) {

        // Trigger resize event informing elements to resize according to the height of the window.
        $timeout(function() {
            angular.element($window).resize();
        }, 0);
    });

    // Keep track of the selected row.
    $scope.selectedRow = [];

    // Configure ng-grid.
    $scope.gridOptions = {
        data: 'users',
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

        // Add a little timeout to enabling the event "selectRow" to be propagated
        $timeout(function() {
            Modal.confirmDelete($scope.selectedRow[0], $scope.users);
        }, 0);
    };

    $scope.edit = function(row) {
        $location.path('/admin/user/edit/' + row.entity.id);
    };

    $scope.$on('filterChanged', function(evt, text) {
        $scope.filteringText = text;
    });
});