/**
 * Show a simple table with column legends
 */
angular.module('myApp.directives').directive('gimsColumnLegends', function() {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with element
        template: '<div class="row">' +
                '    <div class="col-md-6">' +
                '        <table class="table table-condensed" ng-if="legends">' +
                '            <thead>' +
                '                <tr>' +
                '                    <th colspan="2">Legends</th>' +
                '                </tr>' +
                '            </thead>' +
                '            <tr ng-repeat="legend in legends">' +
                '                <td>{{legend.short}}</td>' +
                '                <td>{{legend.long}}</td>' +
                '            </tr>' +
                '        </table>' +
                '    </div>' +
                '</div>',
        // The linking function will add behavior to the template
        link: function() {
        },
        controller: function() {
        }
    };
});
