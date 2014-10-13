/*
Usage :

 <gims-filter
     model='filters'            -> usual model
     name="filters"             -> optional name (reported on url)
     show-result-path="true"    -> show filters paths on autocompletion list
     show-selection-path="false"-> show filters paths on selected list
     generic-color="false"       -> if true, show a color for each filter (genericColor property), if false, show only color property
     multiple                   -> select2 in tag mode (missing means single selection on select2)
     query-params="queryParams" -> params for query. color,genericColor,paths are automatically added depending on above attributes values
     change-url="true"          -> update url with ids on selection change
     show-edit-button="true"    -> show a button that links to /admin/filter/x page
     currentUrl="/"             -> optional, current url to be used on return button
     >
 </gims-filter>
 */
angular.module('myApp.directives').directive('gimsFilter', function() {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with element
        replace: true,
        template: '<div><gims-select ' +
                      'model="model" ' +
                      'name="{{name}}" ' +
                      'api="filter" ' +
                      'queryParams="queryParams" ' +
                      'placeholder="Select filter(s)" ' +
                      'container-css-class="select2list" ' +
                      'custom-selection-template="{{selectionTemplate}}" ' +
                      'custom-result-template="{{resultTemplate}}" ' +
                      'custom-var="{{currentUrl}}" ' +
                      'change-url="changeUrl" ' +
                      'disabled="disabled" ' +
                      'style="width: 100%"> ' +
                  '</gims-select></div>',
        scope: {
            name: '@',
            multiple: '@',
            model: '=',
            queryParams: '=?',
            disabled: '=?',
            changeUrl: '@',
            genericColor: '@',
            showResultPath: '@',
            showSelectionPath: '@',
            showEditButton: '@',
            currentUrl: '@'
        },

        compile: function(tElement, tAttrs) {

            // Add "multiple" attribute to gims-select
            // has to be done in compile function cause if attribute is setted true or false, he's considered as true
            // compile function allow to manipulate attribute before link/controller function and add conditionally this attribute
            if (!_.isUndefined(tAttrs.multiple)) {
                tElement.find('gims-select').attr('multiple', '');
            }

            return {
                pre: function preLink(scope) {

                    scope.showResultPath = !_.isUndefined(scope.showResultPath) ? scope.showResultPath == 'true' ? true : false : true;
                    scope.showSelectionPath = !_.isUndefined(scope.showSelectionPath) ? scope.showSelectionPath == 'true' ? true : false : false;
                    scope.disabled = !_.isUndefined(scope.disabled) ? scope.disabled == 'true' ? true : false : false;
                    scope.genericColor = !_.isUndefined(scope.genericColor) ? scope.genericColor == 'true' ? true : false : false;
                    scope.changeUrl = !_.isUndefined(scope.changeUrl) ? scope.changeUrl == 'true' ? true : false : true;
                    scope.showEditButton = !_.isUndefined(scope.showEditButton) ? scope.showEditButton == 'true' ? true : false : false;

                    var defaultFields = ['bgColor', 'color'];
                    if (scope.genericColor) {
                        defaultFields.push('genericColor');
                    }

                    if (scope.showSelectionPath || scope.showResultPath) {
                        defaultFields.push('paths');
                    }

                    if (!scope.queryParams) {
                        scope.queryParams = {fields: defaultFields.join(',')};
                    } else {
                        var userFields = scope.queryParams.fields ? scope.queryParams.fields.split(',') : [];
                        var finalFields = _.uniq(defaultFields.concat(userFields));
                        scope.queryParams.fields = finalFields.join(',');
                    }

                    if (!scope.queryParams.itemOnce) {
                        scope.queryParams.itemOnce = true;
                    }

                    var getTemplate = function(genericColor, showPath, showEditButton) {
                        var template =  "<div>";

                        var filterColWidth = "col-sm-11 col-md-11";
                        if (showPath) {
                            filterColWidth = 'col-sm-4 col-md-4';
                        }

                        template += "<div class='" + filterColWidth + " select-label select-label-with-icon'>";

                        var color = "color";
                        if (genericColor) {
                            color = 'genericColor';
                        }

                        template += "<i class='fa fa-gims-filter' style='color:[[item." + color + "]];' ></i> [[item.name]]";
                        template += "</div>";

                        if (showPath) {
                            template += "<div class='col-sm-7 col-md-7'>" +
                                        "    <small>" +
                                        "       [[_.map(item.paths, function(path) {return \"<div class='select-label select-label-with-icon'><i class='fa fa-gims-filter'></i> \"+path+\"</div>\";}).join('')]]" +
                                        "    </small>" +
                                        "</div>" ;
                        }

                        if (showEditButton) {
                            var returnUrl = '';
                            if (scope.currentUrl) {
                                returnUrl = "?returnUrl=" + scope.currentUrl;
                            }
                            template += "<div class='col-sm-1 col-md-1 hide-in-results' >" +
                                        "    <a class='btn btn-default btn-sm' href='/admin/filter/edit/[[item.id]]" + returnUrl + "'>" +
                                        "        <i class='fa fa-pencil'></i>" +
                                        "    </a>" +
                                        "</div>";
                        }

                        template += "<div class='clearfix'></div>";
                        template += "</div>";

                        return template;
                    };

                    scope.selectionTemplate = getTemplate(scope.genericColor, scope.showSelectionPath, scope.showEditButton);
                    scope.resultTemplate = getTemplate(scope.genericColor, scope.showResultPath);

                }
            };
        }
    };
});
