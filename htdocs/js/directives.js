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
    });