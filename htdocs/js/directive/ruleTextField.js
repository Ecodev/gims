angular.module('myApp.directives').directive('gimsRuleTextField', function($rootScope, Restangular) {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with tag
        templateUrl: '/template/admin/rule/textField',
        scope: {
            rule: '='
        },
        controller: function($scope) {

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
                    // _session.setMode("ace/mode/sql");
                    _session.setMode("ace/mode/excel");
                }
            };

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
                            ['Formula syntax is invalid and cannot be computed.'
                            ];
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

            /**
             * Watchers and event managers
             */
            $scope.$watch('rule.formula', validate);

            $rootScope.$on('gims-rule-target-selected', function(event, token) {
                aceEditor.insert(token);
            });
        }
    };
});
