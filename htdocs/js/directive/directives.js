/* Directives */

function isEmpty(value) {
    return angular.isUndefined(value) || value === '' || value === null || value !== value;
}

angular.module('myApp.directives')
        .directive('ngVisible', function() {
            return function(scope, elem, attr) {
                scope.$watch(attr.ngVisible, function(value) {
                    elem.css('visibility', value ? 'visible' : 'hidden');
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
        .directive('gimsLinkNew', function($routeParams) {
            return {
                restrict: "E",
                replace: true,
                scope: {
                    content: '='
                },
                link: function(scope, element, attrs) {

                    var returnUrl = '';
                    if (attrs.origin) {
                        returnUrl = sprintf('?returnUrl=/admin/%s/edit/%s&%s=%s',
                                attrs.origin,
                                $routeParams.id,
                                attrs.origin,
                                $routeParams.id
                                );
                    }

                    var html = sprintf('<a class="link-new list-inline btn btn-default" href="/admin/%s/new%s"><i class="fa fa-gims-add"></i> new %s</a>',
                            attrs.target,
                            returnUrl,
                            attrs.target
                            );

                    element.html(html).show();
                }
            };
        })
        .directive('gimsFocus', function($timeout) {
            return {
                link: function(scope, element) {
                    $timeout(function() {
                        element.focus();
                    }, 100);
                }
            };
        })
        /**
         * Deactivate ng-animate on all children elements
         */
        .directive('disableAnimate', function($animate) {

            return function($scope, elem) {
                $animate.enabled(false, elem);
            };
        });
