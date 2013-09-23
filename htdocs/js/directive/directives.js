/* Directives */
angular.module('myApp.directives')
    .directive('appVersion', ['version', function (version) {
        return function (scope, elm) {
            elm.text(version);
        };
    }])

    .directive('ngVisible', function () {
        return function (scope, elem, attr) {
            scope.$watch(attr.ngVisible, function (value) {
                elem.css('visibility', value ? 'visible' : 'hidden');
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
    .directive('gimsLinkNew', function ($compile, $routeParams) {
        return {
            restrict: "E",
            replace: true,
            link: function (scope, element, attrs) {
                var html = sprintf('<a class="link-new" href="/admin/%s/new?returnUrl=/admin/%s/edit/%s&%s=%s&returnTab=%s"><i class="icon-plus-sign"></i> new %s</a>',
                    attrs.target,
                    attrs.origin,
                    $routeParams.id,
                    attrs.origin,
                    $routeParams.id,
                    attrs.returnTab,
                    attrs.target
                );
                element.html(html).show();
            },
            scope: {
                content: '='
            }
        };
    })
    .directive('gimsFocus', function($timeout) {
        return {
            link: function ( scope, element, attrs ) {
                $timeout( function () { element.focus(); } );
            }
        };
    });
