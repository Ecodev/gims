
angular.module('myApp.directives').directive('gimsRelations', function() {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with element
        // This HTML will replace the directive.
        replace: true,
        scope: {
            relation: '@',
            first: '@',
            second: '@',
            third: '@',
            format: '@'
        },
        template: '<div>' +
                '<div ng-grid="gridOptions" class="gridStyle"></div>' +
                '<div class="well form-group" ng-class="{\'has-error\': exists}" ng-hide="isReadOnly">' +
                '<span class="col-md-4">' +
                '<gims-select api="{{second}}" model="secondValue" placeholder="Select a {{second}}" style="width:100%;" format="{{format}}"></gims-select>' +
                '</span>' +
                '<span class="col-md-4">' +
                '<gims-select api="{{third}}" model="thirdValue" placeholder="Select a {{third}}" style="width:100%;"></gims-select>' +
                '</span>' +
                '<span class="col-md-1">' +
                '<button class="btn btn-default" ng-click="add()" ng-class="{disabled: !secondValue || !thirdValue || exists}">Add</button> <i class="fa fa-loading" ng-show="isLoading"></i>' +
                '</span><span class="help-block" ng-show="exists">This relation already exists</span>' +
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
                multiSelect: false,
                columnDefs: [
                    {field: $scope.second + '.name', displayName: capitaliseFirstLetter($scope.second)},
                    {field: $scope.third + '.name', displayName: capitaliseFirstLetter($scope.third), width: '250px'},
                    {width: '70px', cellTemplate: '<button type="button" class="btn btn-default btn-xs" ng-click="delete(row)"><i class="fa fa-trash-o fa-lg"></i></button>'}
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