/* Directives */
angular.module('myApp.directives', [])
        .directive('appVersion', ['version', function(version) {
        return function(scope, elm) {
            elm.text(version);
        };
    }])
        .directive('ngBlur', function() {
    return function(scope, elem, attrs) {
        elem.bind('blur', function() {
            scope.$apply(attrs.ngBlur);
        });
    };
})
        .directive('ngKeyup', function() {
    return function(scope, elem, attrs) {
        elem.bind('keyup', function() {
            scope.$apply(attrs.ngKeyup);
        });
    };
})

        .directive('relations', function() {
    return {
        restrict: 'A', // Only usage possible is with attribute
        // This HTML will replace the directive.
        replace: true,
        transclude: true,
        template: '<div>' +
                '<div ng-grid="gridOptions" class="gridStyle"></div>' +
                '<div class="well" ng-hide="isReadOnly">' +
                '<span class="span4">' +
                '<input name="second" ui-select2="select2.second.list" ng-model="select2.second.selected" data-placeholder="Select a {{second}}" style="width:100%;"/>' +
                '</span>' +
                '<span class="span4">' +
                '<input name="third" ui-select2="select2.third.list" ng-model="select2.third.selected" data-placeholder="Select a {{third}}" style="width:100%;"/>' +
                '</span>' +
                '<span class="span1">' +
                '<button class="btn" ng-click="add()" ng-class="{disabled: !select2.second.selected || !select2.third.list}">Add</button> <i class="icon-loading" ng-show="isLoading"></i>' +
                '</span>' +
                '</div>' +
                '</div>',
        // The linking function will add behavior to the template
        link: function(scope, element, attrs) {
            // nothing to do ?
        },
        controller: function($scope, $attrs, $routeParams, $injector, Modal, Select2Configurator) {

            function capitaliseFirstLetter(string)
            {
                return string.charAt(0).toUpperCase() + string.slice(1);
            }

            var options = $scope.$eval($attrs.relations);
            $scope.second = options.second;
            $scope.third = options.third;

            // Get the REST services
            var Relation = $injector.get(capitaliseFirstLetter(options.relation));
            var Second = $injector.get(capitaliseFirstLetter(options.second));
            var Third = $injector.get(capitaliseFirstLetter(options.third));

            // Configure select boxes for addition
            $scope.isReadOnly = !$routeParams.id;
            Select2Configurator.configure($scope, Second, 'second');
            Select2Configurator.configure($scope, Third, 'third');

            // Configure ng-grid
            $scope.relations = $routeParams.id ? Relation.query({parent: options.first, idParent: $routeParams.id}) : [];
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

                if (!$scope.select2.second.selected || !$scope.select2.third.selected) {
                    return;
                }

                $scope.isLoading = true;
                var data = {};
                data[options.first] = $routeParams.id;
                data[options.second] = $scope.select2.second.selected.id;
                data[options.third] = $scope.select2.third.selected.id;

                var relation = new Relation(data);
                relation.$create(function(newRelation) {
                    $scope.relations.push(newRelation);
                    $scope.isLoading = false;
                });
            };

            // Delete a relation
            $scope.delete = function(row) {
                Modal.confirmDelete(row.entity, {objects: $scope.relations, label: row.entity[options.second].name + ' - ' + row.entity[options.third].name});
            };

        }
    };
});