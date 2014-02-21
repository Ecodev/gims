/**
 * ng-grid wrapper for easier use within GIMS
 *
 */
angular.module('myApp.directives').directive('gimsGrid', function() {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with element
        transclude: true,
        template: '<div ng-grid="privateOptions" class="gridStyle"></div>',
        scope: {
            api: '@',
            parent: '@',
            objects: '=?',
            queryparams: '=',
            options: '='
        },
        // The linking function will add behavior to the template
        link: function(scope, element, attr, ctrl) {
        },
        controller: function($scope, $attrs, Restangular, Modal, $routeParams) {
            $scope.objects = [];

            // Fetch objects, but not too often
            var fetchObjects = _.debounce(function() {
                $scope.$apply(function() {

                    var api;
                    if ($scope.parent) {
                        api = Restangular.one($scope.parent, $routeParams.id).all($attrs.api);
                    } else {
                        api = Restangular.all($attrs.api);
                    }


                    var defaultParameters = {
                        page: $scope.pagingOptions.currentPage,
                        perPage: $scope.pagingOptions.pageSize,
                        q: $scope.options.filterOptions ? $scope.options.filterOptions.filterText : null,
                    };
                    var params = _.merge(defaultParameters, $scope.queryparams);

                    api.getList(params).then(function(data) {
                        $scope.objects = data;
                        $scope.totalCount = data.metadata.totalCount;
                    });
                });

            }, 300);

            $scope.$watch('{a: pagingOptions, b: options.filterOptions.filterText}', fetchObjects, true);

            $scope.totalCount = 0;
            $scope.pagingOptions = {
                pageSizes: [25, 50, 100],
                pageSize: 25,
                currentPage: 1
            };

            var defaultOptions = {
                plugins: [new ngGridFlexibleHeightPlugin({minHeight: 400})],
                data: 'objects',
                totalServerItems: 'totalCount',
                enablePaging: true,
                pagingOptions: $scope.pagingOptions,
                showFooter: true,
                enableColumnResize: true,
                filterOptions: {},
                multiSelect: false,
            };

            var overrideOptions = $scope.$parent.$eval($attrs.options);
            $scope.privateOptions = _.merge(defaultOptions, overrideOptions);


            /**
             * Utility function to delete a row from the grid (and from server)
             * The column template should use: ng-click="remove(row)"
             * @param object row
             * @returns void
             */
            $scope.remove = function(row) {

                var lengthBefore = $scope.objects.length;
                Modal.confirmDelete(row.entity, {objects: $scope.objects}).then(function() {

                    $scope.totalCount -= lengthBefore - $scope.objects.length;
                });
            };
        }
    };
});