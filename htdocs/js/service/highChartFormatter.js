angular.module('myApp.services').factory('HighChartFormatter', function() {
    'use strict';

    return {

        tooltipFormatter: function() {
            // recover the template
            var template = '';
            template += this.series.tooltipOptions.headerFormat ? this.series.tooltipOptions.headerFormat : '';
            template += this.series.tooltipOptions.pointFormat ? this.series.tooltipOptions.pointFormat : '';
            template += this.series.tooltipOptions.footerFormat ? this.series.tooltipOptions.footerFormat : '';

            // find all fields syntax {field}
            var fields = template.match(/(\{.*?\})/g);

            // replace the field by his value using this.field for {field} in formatter context
            var evalValue = function(field) {
                return eval('this.' + field.substring(1, field.length - 1));
            };

            // self design pattern to avoid "this" to be in the forEach context
            var self = this;
            _.forEach(fields, function(field) {
                // recover value using formatter context
                var value = evalValue.call(self, field);

                if (_.isUndefined(value) || _.isNull(value)) {
                    value = '';
                }

                // replace {field} tags by their value
                template = template.replace(field, value);
            });

            // return template
            return template;
        },

        scatterFormatter: function() {
            return $('<span/>').css({color: this.series.color}).text(this.point.name)[0].outerHTML;
        }
    };

});
