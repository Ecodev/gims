'use strict';

/* Filters */

angular.module('myApp.filters')
    .filter('interpolate', ['version', function (version) {
        return function (text) {
            return String(text).replace(/\%VERSION\%/mg, version);
        };
    }])
    .filter('percent', function () {
        return function (number) {
            if (number === null) {
                return "";
            }
            var value = number * 100;
            return Math.round(value * 10) / 10 + '%';
        };
    });

