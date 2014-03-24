/* Controllers */
angular.module('myApp').controller('Admin/Rule/CrudCtrl', function($scope, $routeParams, $location, Modal, Restangular) {
    'use strict';

    ace.config.set('basePath', '/lib/ace-builds/src-noconflict');
    ace.config.set('modePath', '/ace-custom');
    $scope.aceOptions = {
        theme: 'clouds',
        onLoad:
                function(_editor) {

                    // Editor part
                    var _session = _editor.getSession();
                    var _renderer = _editor.renderer;

                    // Options
                    _editor.setReadOnly(true);
                    _editor.setFontSize('14px');
                    _editor.setShowPrintMargin(false);
                    _renderer.setShowGutter(false);
//                    _session.setMode("ace/mode/sql");
                    _session.setMode("ace/mode/excel");
                }
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

    $scope.save = function(redirectTo) {
        $scope.sending = true;

        // First case is for update a rule, second is for creating
        if ($scope.rule.id) {
            $scope.rule.put().then(function() {
                $scope.sending = false;

                if (redirectTo) {
                    $location.path(redirectTo);
                }
            });
        } else {
            Restangular.all('rule').post($scope.rule).then(function(rule) {
                $scope.sending = false;

                if (!redirectTo) {
                    redirectTo = '/admin/rule/edit/' + rule.id;
                }
                $location.path(redirectTo);
            });
        }
    };

    // Delete a rule
    $scope.delete = function() {
        Modal.confirmDelete($scope.rule, {label: $scope.rule.name, returnUrl: returnUrl});
    };


    // Load rule if possible
    if ($routeParams.id) {
        Restangular.one('rule', $routeParams.id).get({fields: 'metadata'}).then(function(rule) {
            $scope.rule = rule;
        });
    } else {
        $scope.rule = {};
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
            {field: 'name', displayName: 'Name', width: '450px'},
            {field: 'formula', displayName: 'Formula'},
            {displayName: '', width: '70px', cellTemplate: '<a class="btn btn-default btn-xs" href="/admin/rule/edit/{{row.entity.id}}" ><i class="fa fa-pencil fa-lg"></i></a>' +
                        '<button type="button" class="btn btn-default btn-xs" ng-click="remove(row)" ><i class="fa fa-trash-o fa-lg"></i></button>'}
        ]
    };

});