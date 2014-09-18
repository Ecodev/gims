angular.module('myApp.services').factory('Percent', function() {

    /**
     * Round with 2 decimals.
     * @param {Number} number
     * @returns {Number}
     */
    function round(number) {
        return Math.round(number * 100) / 100;
    }

    return {
        /**
         * Convert from 0.00-1.00 to 0-100
         * @param {Number} fraction
         * @returns {Number}
         */
        fractionToPercent: function(fraction) {
            if (_.isNumber(fraction)) {
                return round(fraction * 100);
            } else {
                return null;
            }
        },
        /**
         * * Convert from 0-100 to 0.00-1.00
         * @param {Number} percent
         * @returns {Number}
         */
        percentToFraction: function(percent) {
            if (_.isNumber(percent)) {
                return percent / 100;
            } else {
                return null;
            }
        }
    };
});
