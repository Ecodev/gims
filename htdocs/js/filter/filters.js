/* Filters */
angular.module('myApp.filters').filter('interpolate', function(version) {
    'use strict';

    return function(text) {
        return String(text).replace(/\%VERSION\%/mg, version);
    };
}).filter('percent', function() {
    'use strict';

    return function(number) {
        if (typeof number != 'number') {
            return "";
        }
        var value = number * 100;
        return Math.round(value * 10) / 10;
    };
}).filter('integer', function() {
    'use strict';

    return function(number) {
        if (typeof number != 'number') {
            return "";
        }

        return Math.round(number);
    };
});
