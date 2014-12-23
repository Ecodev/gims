angular.module('myApp.directives').directive('gimsGridQuestionnaire', function() {
    'use strict';

    return {
        restrict: 'A', // Only usage possible is with attribute
        // This HTML will replace the directive.
        replace: true,
        transclude: true,
        template:
                '<div>' +
                '    <div class="row show-grid">' +
                '        <div class="col-md-6">' +
                '            <input type="text" ng-model="gridOptions.filterOptions.filterText" placeholder="Search..." class="search form-control" style="width: 400px"/>' +
                '        </div>' +
                '        <div class="col-md-6" style="text-align: right">' +
                '            <gims-link-new origin="survey" target="questionnaire" return-tab="2" ></gims-link-new>' +
                '            <a style="margin-left:5px" href="/export/survey/{{survey.id}}/{{survey.name}}.xslx?questionnaires={{selectedQuestionnaires}}" target="_blank" class="btn btn-default"><i class="fa fa-download"></i> Export selected</a>' +
                '        </div>' +
                '    </div>' +
                '    <gims-grid api="questionnaire" parent="survey" objects="questionnaires" options="gridOptions" queryparams="queryparams"></div>' +
                '</div>',
        // The linking function will add behavior to the template
        link: function() {
            // nothing to do ?
        },
        controller: function($scope, $location) {

            var watchListener = $scope.$watch('questionnaires', function(questionnaires) {
                if (questionnaires && questionnaires.length > 0) {
                    angular.forEach($scope.questionnaires, function(questionnaire) {
                        $scope.$watch(function() {
                            return questionnaire.export;
                        }, function() {

                            $scope.selectedQuestionnaires = '';
                            angular.forEach($scope.questionnaires, function(questionnaire) {
                                if (questionnaire.export) {
                                    $scope.selectedQuestionnaires += questionnaire.id + ',';
                                }
                            });
                        });
                    });
                    watchListener();
                }
            });

            // Configure ui-grid.
            $scope.queryparams = {fields: 'permissions,dateLastAnswerModification,reporterNames,validatorNames,completed,spatial,comments,status'};
            $scope.gridOptions = {
                extra: {
                    // This function will be available for columnDefs by prefixing it
                    //  with "options.extra" because it's the internal structure of <gims-grid>
                    selectAll: function($bool) {
                        angular.forEach($scope.questionnaires, function(questionnaire) {
                            questionnaire.export = $bool;
                        });
                    }
                },
                columnDefs: [
                    {field: 'spatial', displayName: 'Spatial'},
                    {field: 'reporterNames', displayName: 'Filled by'},
                    {field: 'dateLastAnswerModification', displayName: 'Modif.', cellFilter: 'date:"dd MMM yyyy"'},
                    {field: 'validatorNames', displayName: 'Validation by'},
                    {field: 'completed', displayName: 'Completed', cellTemplate:
                                '<div class="progress" style="margin: 5px 5px 0 5px">' +
                                '<div class="progress-bar" ng-style="{width: row.entity[col.field] * 100}"></div>' +
                                '</div>'
                    },
                    {
                        name: 'buttons',
                        displayName: '',
                        width: 120,
                        cellTemplate:
                                '<div style="margin: 5px 5px 0 5px ">' +
                                '<i class="fa fa-grid fa-comment" ng-visible="row.entity.comments" title="{{row.entity.comments}}"></i>' +
                                '<i class="fa fa-grid fa-check-square-o" ng-visible="row.entity.status == \'validated\'" title="Validated"></i>' +
                                '<div class="btn-group">' +
                                '<a class="btn btn-default btn-xs btn-edit" href="/admin/questionnaire/edit/{{row.entity.id}}?returnUrl={{options.extra.returnUrl}}" ng-visible="row.entity.permissions.update"><i class="fa fa-pencil fa-lg"></i></a>' +
                                '<button type="button" class="btn btn-default btn-xs" ng-click="getExternalScopes().remove(row)" ng-visible="row.entity.permissions.delete"><i class="fa fa-trash-o fa-lg"></i></button>' +
                                '</div>' +
                                '</div>'
                    },
                    {
                        field: 'export',
                        displayName: 'Export',
                        width: 60,
                        headerCellTemplate:
                                '<div style="margin: 12px 5px 0 5px;text-align:center">' +
                                '<input type="checkbox" ng-model="selected" ng-change="options.extra.selectAll(selected)" />' +
                                '</div>',
                        cellTemplate:
                                '<div style="margin: 12px 5px 0 5px;text-align:center">' +
                                '<input type="checkbox" ng-model="row.entity.export" />' +
                                '</div>'
                    }
                ]
            };

            $scope.$watch(function() {
                return $location.url();
            }, function() {
                $scope.gridOptions.extra.returnUrl = encodeURIComponent($location.url());
            });
        }
    };
});
