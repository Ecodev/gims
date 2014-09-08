angular.module('myApp.directives').directive('gimsCellMenu', function($dropdown, $rootScope, Utility) {

    return {
        restrict: 'E',
        template: '<span class="input-group-btn">' +
                '    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown-menu">' +
                '        <span ng-switch="getCellType(questionnaire, filter, part.id)">' +
                '            <i ng-switch-when="loading" class="fa fa-fw fa-gims-loading"></i>' +
                '            <i ng-switch-when="error" class="fa fa-fw fa-warning text-warning"></i>' +
                '            <i ng-switch-when="answer" class="fa fa-fw fa-question" tooltip="manually answered"></i>' +
                '            <i ng-switch-when="rule" class="fa fa-fw fa-gims-rule" tooltip="computed with rules" ng-class="{\'text-primary\': tabs.isComputing}"></i>' +
                '            <i ng-switch-when="summand" class="fa fa-fw fa-gims-summand" tooltip="computed with summands" ng-class="{\'text-primary\': tabs.isComputing}"></i>' +
                '            <i ng-switch-when="child" class="fa fa-fw fa-gims-child"  tooltip="computed with children" ng-class="{\'text-primary\': tabs.isComputing}"></i>' +
                '            <i ng-switch-default class="fa fa-fw fa-angle-down"></i>' +
                '        </span>' +
                '    </button>' +
                '</span>',
        replace: true,
        scope: {
            tabs: '=',
            mode: '=',
            questionnaire: '=',
            filter: '=',
            part: '='
        },
        link: function(scope, element) {

            var button = element.find('button');
            button.bind('click', function() {
                $dropdown.open({
                    button: button,
                    templateUrl: '/template/browse/cell-menu/menu',
                    controller: 'CellMenuCtrl',
                    resolve: {
                        tabs: function() {
                            return scope.tabs;
                        },
                        mode: function() {
                            return scope.mode;
                        },
                        questionnaire: function() {
                            return scope.questionnaire;
                        },
                        filter: function() {
                            return scope.filter;
                        },
                        part: function() {
                            return scope.part;
                        }
                    }
                });
            });


            /**
             * Returns the type of cell, to be able to display correct icon
             * @param {questionnaire} questionnaire
             * @param {filter} filter
             * @param {integer} partId
             * @returns {String}
             */
            scope.getCellType = function(questionnaire, filter, partId) {

                if (questionnaire.survey) {

                    var question = questionnaire.survey.questions[filter.id];
                    var answer;
                    if (question && question.answers) {
                        answer = question.answers[partId];
                    }

                    if (question && question.isLoading || (answer && answer.isLoading)) {
                        return 'loading';
                    }

                    var firstValue;
                    if (question && question.filter.values && question.filter.values[partId]) {
                        firstValue = question.filter.values[partId].first;
                    }

                    var usages;
                    if (questionnaire.filterQuestionnaireUsagesByFilterAndPart && questionnaire.filterQuestionnaireUsagesByFilterAndPart[filter.id]) {
                        usages = questionnaire.filterQuestionnaireUsagesByFilterAndPart[filter.id][partId].first.concat(questionnaire.filterQuestionnaireUsagesByFilterAndPart[filter.id][partId].second);
                    }

                    if (answer && answer.error) {
                        return 'error';
                    } else if (answer && Utility.isValidNumber(answer[question.value])) {
                        return 'answer';
                    } else if (usages && usages.length) {
                        return 'rule';
                    } else if (filter.summands && filter.summands.length && Utility.isValidNumber(firstValue)) {
                        return 'summand';
                    } else if (Utility.isValidNumber(firstValue)) {
                        return 'child';
                    }
                }

                return 'nothing';
            };
        }
    };
});