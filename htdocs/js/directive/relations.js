
angular.module('myApp.directives').directive('relations', function() {
    'use strict';

    return {
        restrict: 'A', // Only usage possible is with attribute
        // This HTML will replace the directive.
        replace: true,
        transclude: true,
        template: '<div>' +
                '<div ng-grid="gridOptions" class="gridStyle"></div>' +
                '<div class="well control-group" ng-class="{error: exists}" ng-hide="isReadOnly">' +
                '<span class="span4">' +
                '<input name="second" ui-select2="select2.second.list" ng-model="select2.second.selected" data-placeholder="Select a {{second}}" style="width:100%;"/>' +
                '</span>' +
                '<span class="span4">' +
                '<input name="third" ui-select2="select2.third.list" ng-model="select2.third.selected" data-placeholder="Select a {{third}}" style="width:100%;"/>' +
                '</span>' +
                '<span class="span1">' +
                '<button class="btn" ng-click="add()" ng-class="{disabled: !select2.second.selected || !select2.third.list || exists}">Add</button> <i class="icon-loading" ng-show="isLoading"></i>' +
                '</span><span class="help-inline" ng-show="exists">This relation already exists</span>' +
                '</div>' +
                '</div>',
        // The linking function will add behavior to the template
        link: function(scope, element, attrs) {
            // nothing to do ?
        },
        controller: function($scope, $attrs, $routeParams, Restangular, Modal, Select2Configurator) {

            function capitaliseFirstLetter(string)
            {
                return string.charAt(0).toUpperCase() + string.slice(1);
            }

            var options = $scope.$eval($attrs.relations);
            $scope.second = options.second;
            $scope.third = options.third;

            // Get the REST services
            var Second = Restangular.all(options.second);
            var Third = Restangular.all(options.third);

            // Configure select boxes for addition
            $scope.isReadOnly = !$routeParams.id;
            Select2Configurator.configure($scope, options.second, 'second');
            Select2Configurator.configure($scope, options.third, 'third');

            // Configure ng-grid
            $scope.relations = [];
            if ($routeParams.id) {
                Restangular.one(options.first, $routeParams.id).all(options.relation).getList().then(function(relations) {
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
                    {field: options.second + '.name', displayName: capitaliseFirstLetter(options.second)},
                    {field: options.third + '.name', displayName: capitaliseFirstLetter(options.third), width: '250px'},
                    {width: '90px', cellTemplate: '<button type="button" class="btn btn-mini" ng-click="delete(row)"><i class="icon-trash icon-large"></i></button>'}
                ]
            };

            // Add a relation
            $scope.add = function() {

                if (!$scope.select2.second.selected || !$scope.select2.third.selected || $scope.exists) {
                    return;
                }

                $scope.isLoading = true;
                var data = {};
                data[options.first] = $routeParams.id;
                data[options.second] = $scope.select2.second.selected.id;
                data[options.third] = $scope.select2.third.selected.id;

                Restangular.all(options.relation).post(data).then(function(newRelation) {
                    $scope.relations.push(newRelation);
                    $scope.isLoading = false;
                });
            };

            // Delete a relation
            $scope.delete = function(row) {
                Modal.confirmDelete(row.entity, {objects: $scope.relations, label: row.entity[options.second].name + ' - ' + row.entity[options.third].name});
            };

            // Prevent adding duplicated relations
            $scope.$watch('select2.second.selected.id + ":" + select2.third.selected.id + ":" + relations.length', function() {
                $scope.exists = false;
                if ($scope.select2.second.selected && $scope.select2.third.selected) {

                    angular.forEach($scope.relations, function(relation) {
                        if (relation[options.second].id == $scope.select2.second.selected.id && relation[options.third].id == $scope.select2.third.selected.id) {
                            $scope.exists = true;
                        }
                    });
                }
            });

        }
    };
});