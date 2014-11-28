/* Controllers */
angular.module('myApp').controller('Admin/Rule/CrudCtrl', function($scope, $routeParams, $location, Modal, Restangular) {
    'use strict';

    var fields = {fields: 'metadata,structure'};

    $scope.save = function(redirectTo) {
        $scope.sending = true;

        // First case is for update a rule, second is for creating
        if ($scope.rule.id) {
            $scope.rule.put(fields).then(function() {
                $scope.sending = false;

                if (redirectTo) {
                    $location.path(redirectTo);
                }
            }, function() {
                $scope.sending = false;
            });
        } else {
            Restangular.all('rule').post($scope.rule).then(function(rule) {
                $scope.sending = false;

                if (!redirectTo) {
                    redirectTo = '/admin/rule/edit/' + rule.id;
                }
                $location.path(redirectTo);
            }, function() {
                $scope.sending = false;
            });
        }
    };

    // Delete a rule
    $scope.delete = function() {
        Modal.confirmDelete($scope.rule, {label: $scope.rule.name, returnUrl: returnUrl});
    };

    // Default redirect
    var returnUrl = '/admin/rule';
    if ($routeParams.returnUrl) {
        returnUrl = $routeParams.returnUrl;
    }

    $scope.cancel = function() {
        $location.path(returnUrl).search('returnUrl', null);
    };

    $scope.saveAndClose = function() {
        this.save(returnUrl);
    };

    // Load rule if possible
    if ($routeParams.id) {
        Restangular.one('rule', $routeParams.id).get(fields).then(function(rule) {
            $scope.rule = rule;
        });
    } else {
        $scope.rule = {formula: '='};
    }
});

/**
 * Admin Rule Controller
 */
angular.module('myApp').controller('Admin/RuleCtrl', function($scope) {
    'use strict';

    // Configure gims-grid.
    $scope.gridOptions = {
        columnDefs: [
            {field: 'name', displayName: 'Name', width: 450},
            {field: 'formula', displayName: 'Formula'},
            {name: 'buttons', displayName: '', width: 70, cellTemplate: '<a class="btn btn-default btn-xs" href="/admin/rule/edit/{{row.entity.id}}" ><i class="fa fa-pencil fa-lg"></i></a><button type="button" class="btn btn-default btn-xs" ng-click="getExternalScopes().remove(row)" ><i class="fa fa-trash-o fa-lg"></i></button>'}
        ]
    };

});
