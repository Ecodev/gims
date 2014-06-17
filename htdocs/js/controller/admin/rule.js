/* Controllers */
angular.module('myApp').controller('Admin/Rule/CrudCtrl', function($scope, $routeParams, $location, Modal, Restangular, $timeout) {
    'use strict';
    var fields = {fields: 'metadata,structure'};

    var aceEditor;
    ace.config.set('basePath', '/lib/ace-builds/src-noconflict');
    ace.config.set('modePath', '/ace-custom');
    $scope.aceOptions = {
        theme: 'clouds',
        onLoad:
                function(editor) {
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

    $scope.currentValue = {
        id: 'current',
        name: 'Current'
    };

    $scope.token = {
        filter: $scope.currentValue,
        questionnaire: $scope.currentValue,
        part: $scope.currentValue,
        year: 0
    };

    $scope.availableTokens = [
        {
            name: 'Filter value',
            description: "Reference a filter value.",
            filter: true,
            questionnaire: true,
            part: true,
            rule: false,
            level: true,
            year: false,
            toString: function(config) {
                return '{F#' + config.filter.id + ',Q#' + config.questionnaire.id + ',P#' + config.part.id + (config.level ? ',#L2' : '') + '}';
            }
        },
        {
            name: 'Question label',
            description: 'Reference a question label. If the question has no answer, it will return NULL. When used with ISTEXT(), it can be used to detect if an answer exists.',
            filter: true,
            questionnaire: true,
            part: false,
            rule: false,
            level: false,
            year: false,
            toString: function(config) {
                return '{F#' + config.filter.id + ',Q#' + config.questionnaire.id + '}';
            }
        },
        {
            name: 'Rule value (Calculations/Estimations/Ratios)',
            description: 'Reference a rule value. Typically used to reference a Calculation, Estimation or Ratio. WARNING: The referenced rule must exist and be applied to the specified questionnaire and part, otherwise computation will fail.',
            filter: false,
            questionnaire: true,
            part: true,
            rule: true,
            level: false,
            year: false,
            toString: function(config) {
                return '{R#' + config.rule.id + ',Q#' + config.questionnaire.id + ',P#' + config.part.id + '}';
            }
        },
        {
            name: 'Population value',
            description: 'Reference the population data of the questionnaire\'s country. This is an absolute value expressed in number of persons.',
            filter: false,
            questionnaire: true,
            part: true,
            rule: false,
            level: false,
            year: false,
            toString: function(config) {
                return '{Q#' + config.questionnaire.id + ',P#' + config.part.id + '}';
            }
        },
        {
            name: 'Regression: Filter value',
            description: 'Reference a Filter regression value for a specific part and year. The year is defined by the year currently being computed plus a user-defined offset. To express "1 year earlier" the offset would be -1, and for "3 years later", it would be +3. To stay on the same year, use an offset of 0.',
            filter: true,
            questionnaire: false,
            part: true,
            rule: false,
            level: false,
            year: true,
            toString: function(config) {
                var year = config.year > 0 ? '+' + config.year : config.year;
                return '{F#' + config.filter.id + ',P#' + config.part.id + ',Y' + year + '}';
            }
        },
        {
            name: 'Regression: List of all filter values',
            description: 'Reference a list of available filter values for all questionnaires. The result use Excel array constant syntax (eg: "{1,2,3}"). This should be used with Excel functions such as COUNT() and AVERAGE().',
            filter: true,
            questionnaire: false,
            part: false,
            rule: false,
            level: false,
            year: false,
            toString: function(config) {
                return '{F#' + config.filter.id + ',Q#all}';
            }
        },
        {
            name: 'Regression: Cumulated population',
            description: 'Reference the cumulated population for all current questionnaires for the specified part.',
            filter: false,
            questionnaire: false,
            part: true,
            rule: false,
            level: false,
            year: false,
            toString: function(config) {
                return '{Q#all,P#' + config.part.id + '}';
            }
        },
        {
            name: 'Regression: Current year',
            description: 'Reference the year we are currently computing. This may be useful for very exceptional edge cases, but should be avoided as much as possible.',
            filter: false,
            questionnaire: false,
            part: false,
            rule: false,
            level: false,
            year: false,
            toString: function() {
                return '{Y}';
            }
        },
        {
            name: 'Value if this rule is ignored',
            description: 'Reference the value if computed without this rule. It allows to conditionally apply a rule with syntaxes such as "IF(can_apply_my_rule, compute_some_result, {self})".',
            filter: false,
            questionnaire: false,
            part: false,
            rule: false,
            level: false,
            year: false,
            toString: function() {
                return '{self}';
            }
        }
    ];

    $scope.tokenCanBeInserted = function() {

        if (!$scope.token.selected) {
            return false;
        }

        if ($scope.token.selected.rule && !$scope.token.rule) {
            return false;
        }

        return true;
    };

    $scope.insertToken = function() {
        $timeout(function() {
            var tokenString = $scope.token.selected.toString($scope.token);
            aceEditor.insert(tokenString);
        });
    };

    // Validate formula but not too often
    var validate = _.debounce(function() {

        if (!$scope.rule) {
            return;
        }

        var success = function(a) {
            $scope.messages = [];
            $scope.rule = a;
        };

        var fail = function(response) {
            $scope.messages = response.data.messages;
        };

        var validateFields = fields;
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
            {displayName: '', width: '70px', cellTemplate: '<a class="btn btn-default btn-xs" href="/admin/rule/edit/{{row.entity.id}}" ><i class="fa fa-pencil fa-lg"></i></a><button type="button" class="btn btn-default btn-xs" ng-click="remove(row)" ><i class="fa fa-trash-o fa-lg"></i></button>'}
        ]
    };

});
