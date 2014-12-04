/**
 * Service with common functions to manage /browse/table/filter and its contextual menu
 */
angular.module('myApp.services').factory('TableAssistant', function() {
    'use strict';

    function getThematicHeaderRow(columns, offset) {

        var thematics = _(columns).filter('thematic').groupBy('thematic').value();

        var template = '<div class="tableHeaderRow thematic">';

        template += '<div class="tableHeaderCell" style="width:' + offset + 'px" >&nbsp;</div>';
        _.forEach(thematics, function(columns, thematic) {
            template += '<div class="tableHeaderCell" style="width:' +
                        (columns.length * columns[0].width) + 'px;background:' +
                        columns[0].thematicColor + '">' +
                        thematic +
                        '</div>';
        });
        template += '</div>';

        return template;
    }

    function getPartsHeaderRow(columns, offset) {

        var columnsByThematics = _(columns).filter('thematic').groupBy('thematic').value();

        var template = '<div class="tableHeaderRow parts">';

        template += '<div class="tableHeaderCell" style="width:' + offset + 'px" >&nbsp;</div>';
        _.forEach(columnsByThematics, function(columns) {
            _.forEach(_.groupBy(columns, 'part'), function(columns, part) {
                template += '<div class="tableHeaderCell" style="width:' +
                            (columns[0].width * columns.length) + 'px">' +
                            part +
                            '</div>';
            });
        });

        template += '</div>';

        return template;
    }

    function getFiltersHeaderRow(columns) {

        var template = '<div class="tableHeaderRow filters">';

        _.forEach(columns, function(column) {
            template += '<div class="tableHeaderCell" style="width:' +
                        column.width +
                        'px;background:' +
                        column.filterColor +
                        '" tooltip-position="top" tooltip="' +
                        (column.displayLong ? column.displayLong : column.displayName) +
                        '">' +
                        column.displayName +
                        '</div>';
        });
        template += '</div>';

        return template;
    }

    function getHeaderTemplate(columns) {

        var i = 0;
        var offset = 0;
        while (_.isUndefined(columns[i].thematic)) {
            offset += columns[i].width ? columns[i].width : 100;
            i++;
        }

        var template = '<div class="ui-grid-top-panel">' +
                       '    <div class="ui-grid-header-viewport">' +
                       '    <div class="ui-grid-header-canvas">';

        template += getThematicHeaderRow(columns, offset);
        template += getPartsHeaderRow(columns, offset);
        template += getFiltersHeaderRow(columns);

        template += '       </div>';
        template += '   </div>';
        template += '</div>';

        return template;
    }

    // Return public API
    return {
        getHeaderTemplate: getHeaderTemplate
    };
});
