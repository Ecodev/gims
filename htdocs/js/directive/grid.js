/**
 * ng-grid wrapper for easier use within GIMS
 *
 */
angular.module('myApp.directives').directive('gimsGrid', function() {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with element
        transclude: true,
        template:
                '<div>' +
                '    <div ui-grid="privateOptions" ui-grid-auto-resize class="gridStyle" external-scopes="gridScope"></div>' +
                '<div class="row" style="margin-top: 5px;">' +
                '    <div class="col-md-3">Total items: {{totalCount}}</div>' +
                '    <div class="col-md-6">' +
                '        <nav>' +
                '            <ul class="pager" style="margin: 0px;"> ' +
                '                <li><a href ng-click="previous()">Previous</a></li>' +
                '                <li><input type="number" ng-model="paging.page" min="1" step="1" style="display: inline-block; width: 3em;"></li>' +
                '                <li><a href ng-click="next()">Next</a></li>' +
                '            </ul>' +
                '        </nav>' +
                '    </div>' +
                '    <div class="col-md-3" style="text-align: right">Per page: ' +
                '        <select ng-model="paging.perPage" style="display: inline-block; width: 6em;">' +
                '            <option>25</option>' +
                '            <option>50</option>' +
                '            <option>100</option>' +
                '        </select>' +
                '    </div>' +
                '    </div>' +
                '</div>',
        scope: {
            api: '@',
            parent: '@',
            objects: '=?',
            queryparams: '=',
            options: '='
        },
        // The linking function will add behavior to the template
        link: function() {
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
                        page: $scope.paging.page,
                        perPage: $scope.paging.perPage,
                        q: $scope.options.filterOptions ? $scope.options.filterOptions.filterText : null
                    };
                    var params = _.merge(defaultParameters, $scope.queryparams);

                    api.getList(params).then(function(data) {
                        $scope.objects = data;
                        $scope.totalCount = data.metadata.totalCount;
                    });
                });

            }, 300);

            $scope.$watch('{a: paging, b: options.filterOptions.filterText}', fetchObjects, true);

            $scope.totalCount = 0;
            $scope.paging = {
                perPage: 25,
                page: 1
            };

            var defaultOptions = {
                data: 'objects'
            };

            var overrideOptions = $scope.$parent.$eval($attrs.options);
            $scope.privateOptions = _.merge(defaultOptions, overrideOptions);

            function valid() {
                $scope.paging.page = Math.floor($scope.paging.page);
                if ($scope.paging.page < 1) {
                    $scope.paging.page = 1;
                }
            }

            $scope.previous = function() {
                $scope.paging.page--;
                valid();
            };

            $scope.next = function() {
                $scope.paging.page++;
                valid();
            };

            var defaultScope = {
                /**
                 * Utility function to delete a row from the grid (and from server)
                 * The column template should use: ng-click="getExternalScopes().remove(row)"
                 * @param row
                 * @returns void
                 */
                remove: function(row) {
                    var lengthBefore = $scope.objects.length;
                    Modal.confirmDelete(row.entity, {objects: $scope.objects}).then(function() {
                        $scope.totalCount -= lengthBefore - $scope.objects.length;
                    });
                }
            };

            $scope.gridScope = _.merge(defaultScope, overrideOptions.scope);
        }
    };
});
