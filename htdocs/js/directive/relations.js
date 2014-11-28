/**
 * Directive to manage relations between three or more objects
 * It show the list of relations and allow them to be deleted or added.
 * Basic usage is:
 * <gims-relations relation="UserSurvey" properties="['user', 'survey', 'role']"></gims-relations>
 *
 * The first property is the "main" one. All other properties will be used as columns in relations
 * tables. And they also will be selectable to create new relation (with the current "main" property).
 */
angular.module('myApp.directives').directive('gimsRelations', function() {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with element
        // This HTML will replace the directive.
        replace: true,
        scope: {
            relation: '@',
            format: '@',
            properties: '=',
            justification: '@'
        },
        template:
                '<div class="container-fluid">' +
                '    <div class="row">' +
                '        <div class="col-md-9">' +
                '            <input type="text" ng-model="gridOptions.filterOptions.filterText" placeholder="Search..." class="search" style="width: 400px"/>' +
                '        </div>' +
                '    </div>' +
                '    <gims-grid api="{{relation}}" parent="{{first}}" objects="relations" options="gridOptions" class="row"></gims-grid>' +
                '    <div class="well form-group" ng-class="{\'has-error\': exists}" ng-hide="isReadOnly">' +
                '        <span ng-repeat="prop in otherProperties" class="col-md-4">' +
                '            <gims-select api="{{prop}}" model="values[$index]" placeholder="Select a {{prop}}" style="width:100%;"></gims-select>' +
                '        </span>' +
                '        <span class="col-md-4" ng-show="justification">' +
                '            <input type="text" ng-model="justificationValue" placeholder="Justification..." />' +
                '        </span>' +
                '        <span class="col-md-1">' +
                '            <button class="btn btn-default" ng-click="add()" ng-class="{disabled: !canAdd}">Add</button> <i class="fa fa-gims-loading" ng-show="isLoading"></i>' +
                '        </span><span class="help-block" ng-show="exists">This relation already exists</span>' +
                '    </div>' +
                '</div>',
        // The linking function will add behavior to the template
        link: function() {
            // nothing to do ?
        },
        controller: function($scope, $routeParams, Restangular, Modal) {
            function capitaliseFirstLetter(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            }

            $scope.first = _.first($scope.properties);
            $scope.otherProperties = _.rest($scope.properties);
            $scope.values = [];

            // Configure select boxes for addition
            $scope.isReadOnly = !$routeParams.id;

            // Build columns definitions based on properties
            var columnDefs = _.map($scope.otherProperties, function(p) {
                var def = {
                    field: p + '.name',
                    displayName: capitaliseFirstLetter(p)
                };

                // If editable objects, make them a link to their admin page
                if (_.contains(['user', 'survey', 'questionnaire', 'filterSet', 'filter', 'rule'], p)) {
                    def.cellTemplate = '<div class="ui-grid-cell-contents"><a href="/admin/' + p + '/edit/{{row.entity.' + p + '.id}}">{{row.entity.' + p + '.name}}</a></div>';
                }

                return def;
            });

            if ($scope.justification) {
                columnDefs.push({field: 'justification', displayName: 'Justification'});
            }

            columnDefs.push({name: 'buttons', displayName: '', width: 70, cellTemplate: '<button type="button" class="btn btn-default btn-xs" ng-click="getExternalScopes().remove(row)"><i class="fa fa-trash-o fa-lg"></i></button>'});

            $scope.gridOptions = {
                scope: {
                    remove: function(row) {

                        // Concatenate all properties names
                        var label = _.map($scope.otherProperties, function(p) {
                            return row.entity[p].name;
                        }).join(' - ');

                        Modal.confirmDelete(row.entity, {objects: $scope.relations, label: label});
                    }
                },
                columnDefs: columnDefs
            };

            // Add a relation
            $scope.add = function() {
                if (!$scope.canAdd) {
                    return;
                }

                $scope.isLoading = true;
                var data = {};
                data[$scope.first] = $routeParams.id;
                _.forEach($scope.otherProperties, function(p, index) {
                    data[p] = $scope.values[index].id;
                });

                if ($scope.justification) {
                    data.justification = $scope.justificationValue;
                }

                Restangular.all($scope.relation).post(data).then(function(newRelation) {
                    $scope.relations.push(newRelation);
                    $scope.isLoading = false;

                    // Reset last select2 option
                    _.last($scope.otherProperties, function(p, index) {
                        $scope.values[index] = null;
                    });
                });
            };

            // Build an expression to watch all values and relation list
            var watcher = _.reduce($scope.otherProperties, function(result, p, index) {
                return result + 'values[' + index + '].id + ":" + ';
            }, '');
            watcher += 'relations.length + justificationValue';

            // Prevent adding duplicated relations
            $scope.$watch(watcher, function() {
                $scope.exists = false;
                $scope.canAdd = false;

                var allValuesDefined = _.reduce($scope.otherProperties, function(result, p, index) {
                    return result && !_.isUndefined($scope.values[index]);
                });

                if (allValuesDefined) {

                    angular.forEach($scope.relations, function(relation) {
                        var isSameRelation = _.reduce($scope.otherProperties, function(result, p, index) {
                            return result && ($scope.values[index] && relation[p].id == $scope.values[index].id);

                        }, true);

                        if (isSameRelation) {
                            $scope.exists = true;
                        }
                    });

                    // We can add if everything is selected, and is not duplicate
                    $scope.canAdd = !$scope.exists && !$scope.justification || $scope.justificationValue;
                }
            }, true);

        }
    };
});
