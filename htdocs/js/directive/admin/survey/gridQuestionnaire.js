angular.module('myApp.directives').directive('gimsGridQuestionnaire', function () {
    'use strict';

    return {
        restrict: 'A', // Only usage possible is with attribute
        // This HTML will replace the directive.
        replace: true,
        transclude: true,
        template:
            '<div>' +
                '<div class="row show-grid">' +
                    '<div class="col-md-6">' +
                        '<input type="text" ng-model="gridOptions.filterOptions.filterText" placeholder="Search..." class="search" style="width: 400px"/>' +
                    '</div>' +
                    '<div class="col-md-6" style="text-align: right">' +
                        '<gims-link-new origin="survey" target="questionnaire" return-tab="2" ></gims-link-new>'+
                        '<div style="margin-left:10px" class="list-inline btn-group">'+
                            '<button ng-click="selectAll(true)"  class="btn btn-default"><i class="fa fa-check-square-o"></i> Select all</button>'+
                            '<button ng-click="selectAll(false)"  class="btn btn-default"><i class="fa fa-square-o"></i> Unselect all</button>'+
                            '<a href="/export/survey/{{survey.id}}/{{survey.name}}.xslx?questionnaires={{selectedQuestionnaires}}" target="_blank" class="btn btn-default"><i class="fa fa-download"></i> Export selected</a>'+
                        '</div>'+
                    '</div>' +
                '</div>' +
                '<div ng-grid="gridOptions" class="gridStyle"></div>' +
            '</div>',
        // The linking function will add behavior to the template
        link: function () {
            // nothing to do ?
        },
        controller: function ($scope, $location, $resource, $routeParams, Restangular, Modal) {


            $scope.$watch(function(){ return $location.url(); }, function(){
                $scope.returnUrl = encodeURIComponent($location.url());
            });


//            $scope.selectedQuestionnaires = '';

            var watchListener = $scope.$watch('questionnaires', function(questionnaires) {
                if(questionnaires && questionnaires.length > 0) {
                    angular.forEach($scope.questionnaires, function(questionnaire) {
                        $scope.$watch(function(){
                            return questionnaire.export;
                        },function(){

                            $scope.selectedQuestionnaires = '';
                            angular.forEach($scope.questionnaires, function(questionnaire) {
                                if (questionnaire.export) {
                                    $scope.selectedQuestionnaires += questionnaire.id+',';
                                }
                            });
                        });
                    });
                    watchListener();
                }
            });

            // Delete a questionnaire
            $scope.removeQuestionnaire = function (row) {
                var Questionnaire = new $resource('/api/questionnaire'); // TODO: find out a way to it with restangular instead of $resource
                var questionnaire = new Questionnaire(row.entity);
                Modal.confirmDelete(questionnaire, {objects: $scope.questionnaires, label: questionnaire.name, returnUrl: $location.path()});
            };

            // Keep track of the selected row.
            $scope.selectedRow = [];

            $scope.selectAll = function($bool)
            {
                _.forEach($scope.questionnaires, function(questionnaire) {
                    questionnaire.export = $bool;
                });
            }

            // Configure ng-grid.
            $scope.gridOptions = {
                plugins: [new ngGridFlexibleHeightPlugin({minHeight: 100})],
                data: 'questionnaires',
                enableCellSelection: true,
                showFooter: false,
                selectedItems: $scope.selectedRow,
                filterOptions: {},
                multiSelect: false,
                columnDefs: [
                    {field: 'spatial', displayName: 'Spatial'},
                    {field: 'reporterNames', displayName: 'Filled by'},
                    {field: 'dateLastAnswerModification', displayName: 'Modif.', cellFilter: 'date:"dd MMM yyyy"'},
                    {field: 'validatorNames', displayName: 'Validation by'},
                    {field: 'completed', displayName: 'Completed', cellTemplate:
                        '<div class="progress" style="margin: 5px 5px 0 5px">' +
                            '<div class="progress-bar" ng-style="{width: row.entity[col.field] * 100}"></div>' +
                        '</div>'},
                    {displayName: '', width: '120px', cellTemplate:
                        '<div style="margin: 5px 5px 0 5px ">' +
                            '<i class="fa fa-grid fa-comment" ng-visible="row.entity.comments" data-toggle="tooltip" title="{{row.entity.comments}}"></i>' +
                            '<i class="fa fa-grid fa-check-square-o" ng-visible="row.entity.status == \'validated\'" title="Validated"></i>' +
                            '<div class="btn-group">'+
                                '<a class="btn btn-default btn-xs btn-edit" href="/admin/questionnaire/edit/{{row.entity.id}}?returnUrl={{returnUrl}}" ng-visible="row.entity.permissions.update"><i class="fa fa-pencil fa-lg"></i></a>' +
                                '<button type="button" class="btn btn-default btn-xs" ng-click="removeQuestionnaire(row)"  ng-visible="row.entity.permissions.delete"><i class="fa fa-trash-o fa-lg"></i></button>' +
                            '</div>'+
                        '</div>'
                    },
                    {field: 'select', displayName: 'Export', width: '60px', cellTemplate :
                        '<div style="margin: 12px 5px 0 5px;text-align:center">' +
                            '<input type="checkbox" ng-model="row.entity.export" />'+
                        '</div>'
                    }
                ]
            };

            Restangular
                .one('survey', $routeParams.id)
                .all('questionnaire')
                .getList({fields: 'permissions,dateLastAnswerModification,reporterNames,validatorNames,completed,spatial,comments,status'})
                .then(function (questionnaires) {
                    $scope.questionnaires = questionnaires;
                });
        }
    };
});