angular.module('myApp.directives').directive('percent', function() {
    return {
        restrict: 'A',
        require: 'ngModel',
        scope: {
            percent: '=',
            percentPlaceholder: '@'
        },
        link: function(scope, element, attr, ngModel) {
            if (!ngModel) {
                return;
            }

            /**
             * Round with 3 decimals.
             */
            function round3decimals(val) {
                if (val) {
                    val = Math.round(val * 1000) / 1000;
                    return val;
                }
                return '';
            }

            /**
             * if percent, divide by 100 (don't round, because we need full entered value in model)
             */
            function toModel(val) {
                if (scope.isPercent) {
                    if (val) {
                        val = val / 100;
                    } else {
                        val = null;
                    }
                }
                return val;
            }

            /**
             * if percent, multiply by 100 and format with 3 decimals
             */
            function toInput(val) {
                if (val) {
                    if (scope.isPercent) {
                        val *= 100;
                    }
                    return round3decimals(val);
                }
            }

            /**
             * Add parsers to ngModelCtrl when first percent value (bool) is received
             * Then just set viewValue as new viewValue (happens when swap between percent and value)
             *  -> when changing, viewValue stay the same but the modelValue changes
             */
            scope.$watch('percent', function(newAbsolute, oldAbsolute) {
                scope.isPercent = !_.isUndefined(newAbsolute) ? !newAbsolute : newAbsolute;
                if (!_.isUndefined(scope.isPercent) && _.isUndefined(oldAbsolute)) {
                    ngModel.$parsers.push(toModel);
                    ngModel.$formatters.push(toInput);
                    ngModel.$modelValue = toInput(ngModel.$modelValue);
                    ngModel.$render();
                } else {
                    ngModel.$setViewValue(ngModel.$viewValue);
                }
            });

            scope.$watch('percentPlaceholder', function() {
                var placeholder = !_.isUndefined(scope.percentPlaceholder) ? round3decimals(scope.percentPlaceholder * 100) : '';
                element.attr('placeholder', placeholder);
            });
        }
    };
});
