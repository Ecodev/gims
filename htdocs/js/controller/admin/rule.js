/* Controllers */
angular.module('myApp').controller('Admin/Rule/CrudCtrl', function($scope, $routeParams, $location, Modal, Restangular, $rootScope) {
    'use strict';
    var fields = {fields: 'metadata,structure'};

    var aceEditor;
    ace.config.set('basePath', '/lib/ace-builds/src-noconflict');
    ace.config.set('modePath', '/ace-custom');
    $scope.aceOptions = {
        theme: 'clouds',
        onLoad: function(editor) {
            aceEditor = editor; // Keep a reference to editor for later

            // Editor part
            var _session = aceEditor.getSession();
            var _renderer = aceEditor.renderer;
            // Options
            aceEditor.setReadOnly(true);
            aceEditor.setFontSize('14px');
            aceEditor.setShowPrintMargin(false);
            _renderer.setShowGutter(false);
            //                    _session.setMode("ace/mode/sql");
            _session.setMode("ace/mode/excel");
        }
    };

    $rootScope.$on('gims-rule-target-selected', function(event, token) {
        aceEditor.insert(token);
    });

    // Validate formula but not too often
    var validate = _.debounce(function() {

        if (!$scope.rule) {
            return;
        }

        var success = function(validatedRule) {
            $scope.messages = [];
            $scope.rule = validatedRule;
        };

        var fail = function(response) {
            if (response.data.messages) {
                $scope.messages = response.data.messages;
            } else {
                // If PHPExcel threw fatal errors, we default to generic message
                $scope.messages =
                    ['Formula syntax is invalid and cannot be computed.'];
            }
        };

        var validateFields = _.clone(fields);
        validateFields.validate = true;
        if ($scope.rule.id) {
            $scope.rule.put(validateFields).then(success, fail);
        } else {
            Restangular.all('rule').post($scope.rule, validateFields).then(success, fail);
        }
    }, 300);

    $scope.$watch('rule.formula', validate);

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
            {field: 'name', displayName: 'Name', width: '450px'},
            {field: 'formula', displayName: 'Formula'},
            {displayName: '', width: '70px', cellTemplate: '<a class="btn btn-default btn-xs" href="/admin/rule/edit/{{row.entity.id}}" ><i class="fa fa-pencil fa-lg"></i></a><button type="button" class="btn btn-default btn-xs" ng-click="remove(row)" ><i class="fa fa-trash-o fa-lg"></i></button>'}
        ]
    };

});
