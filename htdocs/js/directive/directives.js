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

                    var html = sprintf('<a class="link-new list-inline btn btn-default" href="/admin/%s/new%s"><i class="fa fa-plus-circle"></i> new %s</a>',
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
        .directive('ngMin', function() {
            return {
                restrict: 'A',
                require: 'ngModel',
                link: function(scope, elem, attr, ctrl) {
                    scope.$watch(attr.ngMin, function(){
                        ctrl.$setViewValue(ctrl.$viewValue);
                    });
                    var minValidator = function(value) {
                        var min = scope.$eval(attr.ngMin) || 0;
                        if (!isEmpty(value) && value < min) {
                            ctrl.$setValidity('ngMin', false);
                            return undefined;
                        } else {
                            ctrl.$setValidity('ngMin', true);
                            return value;
                        }
                    };

                    ctrl.$parsers.push(minValidator);
                    ctrl.$formatters.push(minValidator);
                }
            };
        })
        .directive('ngMax', function() {
            return {
                restrict: 'A',
                require: 'ngModel',
                link: function(scope, elem, attr, ctrl) {
                    scope.$watch(attr.ngMax, function(){
                        ctrl.$setViewValue(ctrl.$viewValue);
                    });
                    var maxValidator = function(value) {
                        var max = scope.$eval(attr.ngMax) || Infinity;
                        if (!isEmpty(value) && value > max) {
                            ctrl.$setValidity('ngMax', false);
                            return undefined;
                        } else {
                            ctrl.$setValidity('ngMax', true);
                            return value;
                        }
                    };

                    ctrl.$parsers.push(maxValidator);
                    ctrl.$formatters.push(maxValidator);
                }
            };
        });



