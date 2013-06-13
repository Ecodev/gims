
angular.module('myApp.directives').directive('gimsRelations', function() {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with attribute
        // This HTML will replace the directive.
        replace: true,
        scope: {
            relation: '@',
            first: '@',
            second: '@',
            third: '@'
        },
        template: '<div>' +
                '<div ng-grid="gridOptions" class="gridStyle"></div>' +
                '<div class="well control-group" ng-class="{error: exists}" ng-hide="isReadOnly">' +
                '<span class="span4">' +
                '<gims-select api="{{second}}" model="secondValue" placeholder="Select a {{second}}" style="width:100%;"></gims-select>' +
                '</span>' +
                '<span class="span4">' +
                '<gims-select api="{{third}}" model="thirdValue" placeholder="Select a {{third}}" style="width:100%;"></gims-select>' +
                '</span>' +
                '<span class="span1">' +
                '<button class="btn" ng-click="add()" ng-class="{disabled: !secondValue || !thirdValue || exists}">Add</button> <i class="icon-loading" ng-show="isLoading"></i>' +
                '</span><span class="help-inline" ng-show="exists">This relation already exists</span>' +
                '</div>' +
                '</div>',
        // The linking function will add behavior to the template
        link: function(scope, element, attrs) {
            // nothing to do ?
        },
        controller: function($scope, $routeParams, Restangular, Modal) {
            function capitaliseFirstLetter(string)
            {
                return string.charAt(0).toUpperCase() + string.slice(1);
            }

            // Configure select boxes for addition
            $scope.isReadOnly = !$routeParams.id;

            // Configure ng-grid
            $scope.relations = [];
            if ($routeParams.id) {
                Restangular.one($scope.first, $routeParams.id).all($scope.relation).getList().then(function(relations) {
                    $scope.relations = relations;
                });
            }
            $scope.gridOptions = {
                plugins: [new ngGridFlexibleHeightPlugin({minHeight: 100})],
                data: 'relations',
                filterOptions: {
                    filterText: 'filteringText',
                    useExternalFilter: false
                },
                multiSelect: false,
                columnDefs: [
                    {field: $scope.second + '.name', displayName: capitaliseFirstLetter($scope.second)},
                    {field: $scope.third + '.name', displayName: capitaliseFirstLetter($scope.third), width: '250px'},
                    {width: '90px', cellTemplate: '<button type="button" class="btn btn-mini" ng-click="delete(row)"><i class="icon-trash icon-large"></i></button>'}
                ]
            };

            // Add a relation
            $scope.add = function() {
                if (!$scope.secondValue || !$scope.thirdValue || $scope.exists) {
                    return;
                }

                $scope.isLoading = true;
                var data = {};
                data[$scope.first] = $routeParams.id;
                data[$scope.second] = $scope.secondValue.id;
                data[$scope.third] = $scope.thirdValue.id;

                Restangular.all($scope.relation).post(data).then(function(newRelation) {
                    $scope.relations.push(newRelation);
                    $scope.isLoading = false;
                    $scope.thirdValue = null; // Reset last select2 option
                });
            };

            // Delete a relation
            $scope.delete = function(row) {
                Modal.confirmDelete(row.entity, {objects: $scope.relations, label: row.entity[$scope.second].name + ' - ' + row.entity[$scope.third].name});
            };

            // Prevent adding duplicated relations
            $scope.$watch('secondValue.id + ":" + thirdValue.id + ":" + relations.length', function() {
                $scope.exists = false;
                if ($scope.secondValue && $scope.thirdValue) {

                    angular.forEach($scope.relations, function(relation) {
                        if (relation[$scope.second].id == $scope.secondValue.id && relation[$scope.third].id == $scope.thirdValue.id) {
                            $scope.exists = true;
                        }
                    });
                }
            });

        }
    };
});