'use strict';

/* Controllers */
angular.module('myApp').controller('AdminCtrl', function () {

});


angular.module('myApp').controller('Admin/Survey/EditCtrl', function ($scope, $routeParams, $location, $dialog, Survey) {

    $scope.actives = [
        {text: 'Yes', value: 'true'},
        {text: 'No', value: 'false'}
    ];

    // Defining label for GUI.
    $scope.sending = false;
    $scope.sendLabel = 'Save';

    $scope.save = function () {
        $scope.sending = true;
        $scope.sendLabel = 'Saving...';

        // First case is for update a survey, second is for creating
        if ($scope.survey.id > 0) {
            $scope.survey.$update({id: $scope.survey.id}, function (survey) {
                $location.path('/admin/survey');
            });
        } else {
            $scope.survey = new Survey($scope.survey);
            $scope.survey.$create(function (survey) {
                $location.path('/admin/survey');
            });
        }
    };

    // Delete a survey
    $scope.delete = function () {
        var title = 'Confirmation delete';
        var msg = 'You are going to delete survey "' + $scope.survey.code + '". Are you sure?';
        var btns = [
            {result: 'cancel', label: 'Cancel'},
            {result: 'ok', label: 'OK', cssClass: 'btn-primary'}
        ];
        $dialog.messageBox(title, msg, btns)
            .open()
            .then(function (result) {
                if (result === 'OK') {
                    $scope.survey.$delete({id: $scope.survey.id}, function () {
                        $location.path('/admin/survey');
                    });
                }
            });
    };

    // Load survey if possible
    if ($routeParams.id > 0) {
        Survey.get({id: $routeParams.id}, function (survey) {

            // cast a few variable
            survey.year -= 0; // int value
            survey.active += ''; // string value
            $scope.survey = new Survey(survey);
        });
    }
});

angular.module('myApp').controller('Admin/SurveyCtrl', function ($scope, $routeParams, $location, $window, $timeout, ConfirmDelete, Survey) {

    ConfirmDelete.test(123);

    // Initialize
    $scope.filteringText = '';

    $scope.filterOptions = {
        filterText: 'filteringText',
        useExternalFilter: false
    };

    $scope.surveys = Survey.query(function (data) {

        // Trigger resize event informing elements to resize according to the height of the window.
        $timeout(function () {
            angular.element($window).resize();
        }, 0)
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
            {displayName: '', cellTemplate: '<button type="button" class="btn btn-mini" ng-click="edit(row)" ><i class="icon-pencil"></i></button>' +
                '<button type="button" class="btn btn-mini" ng-click="delete(row)" ><i class="icon-trash"></i></button>'}
        ]
    };

    $scope.delete = function (row) {

        // Add a little timeout to give time to the event "selectRow" to be propagated
        $timeout(function () {
            console.log($scope.selectedRow[0].code);
        }, 0)
    }

    $scope.edit = function (row) {
        $location.path('/admin/survey/edit/' + row.entity.id);
    }

    $scope.$on('filterChanged', function (evt, text) {
        $scope.filteringText = text;
    });
});