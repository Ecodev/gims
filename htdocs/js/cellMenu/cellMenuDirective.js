angular.module('myApp.directives').directive('gimsCellMenu', function($modal, $position, $log, $rootScope, Utility) {

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
        link: function(scope, element, attrs) {

            element.bind('click', function(event) {

                var modalInstance = $modal.open({
                    templateUrl: '/template/browse/cell-menu/menu',
                    windowTemplateUrl: '/template/browse/cell-menu/window',
                    controller: 'CellMenuCtrl',
                    backdrop: false,
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

                /**
                 * Position the menu under the button which called it
                 * @param {element} menu
                 * @returns {void}
                 */
                function positionMenu(menu) {
                    var target = $(event.target).closest('button');
                    var targetPosition = $position.offset(target);
                    var menuPosition = {
                        top: targetPosition.top + targetPosition.height + 'px',
                        left: targetPosition.left + 'px'
                    };
                    menu.css(menuPosition);
                }

                var stopListening = $rootScope.$on('gims-contextual-menu-rendered', function(event, menu) {
                    positionMenu(menu);
                    stopListening();
                });

                modalInstance.result.then(function(selectedItem) {
                    scope.selected = selectedItem;
                }, function() {
                    $log.info('Modal dismissed at: ' + new Date());
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
                        usages = questionnaire.filterQuestionnaireUsagesByFilterAndPart[filter.id][partId];
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
}).directive('gimsPositionWhenRendered', function($rootScope) {
    return function(scope, element) {
        $rootScope.$emit('gims-contextual-menu-rendered', element);
    };
});
