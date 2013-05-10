/* Directives */
angular.module('myApp.directives', [])
    .directive('appVersion', ['version', function (version) {
        return function (scope, elm) {
            elm.text(version);
        };
    }])
    .directive('ngBlur', function () {
        return function (scope, elem, attrs) {
            elem.bind('blur', function () {
                scope.$apply(attrs.ngBlur);
            });
        };
    })
    .directive('ngKeyup', function () {
        return function (scope, elem, attrs) {
            elem.bind('keyup', function () {
                scope.$apply(attrs.ngKeyup);
            });
        };
    })
    .directive('resize', function ($window) {
        return function (scope) {

            var w = angular.element($window);
            scope.getWindowDimensions = function () {
                return { 'h': w.height(), 'w': w.width() };
            };
            scope.$watch(scope.getWindowDimensions, function (newValue) {

                // resize Grid to optimize height
                $('.gridStyle').height(newValue.h - 250);
            }, true);

            w.bind('resize', function () {
                scope.$apply();
            });
        };
    });