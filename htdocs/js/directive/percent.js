angular.module('myApp.directives').directive('percent', function($timeout) {
    return {
        restrict: 'A',
        require: 'ngModel',
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

                return val;
            }

            /**
             * Add parsers to ngModelCtrl when first percent value (bool) is received
             * Then just set viewValue as new viewValue (happens when swap between percent and value)
             *  -> when changing, viewValue stay the same but the modelValue changes
             */
            var added = false;
            attr.$observe('percent', function(newAbsolute) {

                if (newAbsolute == 'true') {
                    scope.isPercent = false;
                } else if (newAbsolute == 'false') {
                    scope.isPercent = true;
                } else {
                    scope.isPercent = undefined;
                }

                $timeout(function() {
                    if (!_.isUndefined(scope.isPercent) && !added) {
                        added = true;
                        ngModel.$parsers.push(toModel);
                        ngModel.$formatters.push(toInput);
                        ngModel.$modelValue = toInput(ngModel.$modelValue);
                        ngModel.$render();
                    }
                }, 2000);

            });

            attr.$observe('percentPlaceholder', function(newPlaceholder) {
                /** @todo : all rules are considered as percent, so *100 is hardcoded. Ideally should not be, but we can't know if the result of a formula is percent or absolute */
                var placeholder = !_.isUndefined(newPlaceholder) ? round3decimals(newPlaceholder * 100) : '';
                element.attr('placeholder', placeholder);
            });
        }
    };
});
