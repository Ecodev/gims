
angular.module('myApp.directives').directive('gimsContributeQuestionnaireJmp', function() {
    'use strict';

    return {
        restrict: 'E', // Only usage possible is with element
        // This HTML will replace the directive.
        template: '<div ng-grid="gridOptions" class="gridStyle"></div>',
        // The linking function will add behavior to the template
        link: function() {
            // nothing to do ?
        },
        controller: function($scope, Restangular, $timeout) {

            // Update Answer method
            $scope.updateAnswer = function(column, row) {

                var reg = new RegExp('[0-9]+', "g");
                var answerIndex = reg.exec(column.field)[0];
                var question = row.entity;
                var answer = question.answers[answerIndex];

                // If the answer is undefined, that means it's an invalid value that should not be saved
                if (_.isUndefined(answer.valuePercent)) {

                    // If it's an existing answer, restore valid answer from DB
                    if (answer.id) {
                        answer.isLoading = true;
                        Restangular.one('answer', answer.id).get().then(function(answer) {
                            question.answers[answerIndex] = answer;
                        });
                    }

                } else {

                    // If existing answer, update it
                    answer.isLoading = true;
                    if (answer.id) {
                        answer.put().then(function(answer) {
                            question.answers[answerIndex] = answer;
                        });

                        // Otherwise, create it
                    } else {
                        Restangular.all('answer').post(answer).then(function(answer) {
                            question.answers[answerIndex] = answer;
                        });
                    }

                }
            };

            // Template for cell editing with input "number".
            var cellEditableTemplate = '<form name="myForm" ng-class="{\'has-error\': myForm.answerValue.$invalid}" has-error><input name="answerValue" ng-class="\'colt\' + col.index" ng-input="COL_FIELD" ng-model="COL_FIELD" step="any" min="0" max="1" type="number" style="width: 90%" ng-blur="updateAnswer(col, row)" /></form>';

            // Configure ng-grid.
            var gridLayoutPlugin = new ngGridLayoutPlugin();
            $scope.gridOptions = {
                data: 'questions',
                plugins: [gridLayoutPlugin],
                enableCellSelection: true,
                showFooter: false,
                multiSelect: false,
                columnDefs: [
                    {field: 'name', displayName: 'Question', width: '500px'},
                    {field: 'answers.1.valuePercent', displayName: 'Urban', enableCellEdit: true, cellFilter: 'percent', editableCellTemplate: cellEditableTemplate}, //, cellTemplate: 'cellTemplate.html'
                    {field: 'answers.2.valuePercent', displayName: 'Rural', enableCellEdit: true, cellFilter: 'percent', editableCellTemplate: cellEditableTemplate},
                    {field: 'answers.3.valuePercent', displayName: 'Total', enableCellEdit: true, cellFilter: 'percent', editableCellTemplate: cellEditableTemplate},
                    {displayName: '', cellTemplate: '<div ng-show="row.entity.answers.1.isLoading || row.entity.answers.2.isLoading || row.entity.answers.3.isLoading"><i class="fa fa-gims-loading" /></div>', cellClass: 'column-loading', width: '28px'}
                ]
            };

            // Here we need to tell ng-grid to refresh, because it was initially hidden
            // and it could layout properly
            $scope.$watch('isLoading', function() {
                $timeout(function() {
                    gridLayoutPlugin.updateGridLayout();
                }, 0);

            });

        }
    };
});
