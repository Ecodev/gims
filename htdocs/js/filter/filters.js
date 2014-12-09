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
}).filter('capital', function() {
    'use strict';

    return function(text) {

        if (text) {
            return text.charAt(0).toUpperCase() + text.slice(1);
        }

        return text;
    };
}).filter('prettifyCamelCase', function() {
    'use strict';

    return function(value) {

        var output = "";
        var len = value.length;
        var char;

        for (var i = 0; i < len; i++) {
            char = value.charAt(i);

            if (i === 0) {
                output += char.toUpperCase();
            } else if (char !== char.toLowerCase() && char === char.toUpperCase()) {
                output += " " + char.toLowerCase();
            } else if (char == "-" || char == "_") {
                output += " ";
            } else {
                output += char;
            }
        }

        return output;
    };
}).filter('checkmark', function() {
    return function(input) {
        return input ? '\u2714' : '\u2718';
    };
}).filter('toString', function() {
    return function(input) {
        return String(input);
    };
})
;
