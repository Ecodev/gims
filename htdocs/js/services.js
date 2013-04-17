'use strict';

/* Services */

/**
 * Simple service returning the version of the application
 */
angular.module('myApp.services', [])
    .value('version', '0.3');

/**
 * Resource service
 */
angular.module('myApp.resourceServices', ['ngResource'])
    .factory('Questionnaire', function ($resource) {
        return $resource('/api/questionnaire/:id');
    })
    .factory('Survey', function ($resource) {
        return $resource('/api/survey/:id', {}, {
            create: {
                method: 'POST'
            },
            update: {
                method: 'PUT'
            }
        });
    })
    .factory('Question', function ($resource) {
        return $resource('/api/questionnaire/:idQuestionnaire/question/:id', {}, {
            update: {
                method: 'PUT'
            }
        });
    })
    .factory('Answer', function ($resource) {
        return $resource('/api/answer/:id', {}, {
            create: {
                method: 'POST'
            },
            update: {
                method: 'PUT'
            }
        });
    });

/**
 * Admin Survey service
 */
angular.module('myApp.adminSurveyServices', [])
    .factory('ConfirmDelete', function ($dialog, $location) {
        return {
            show: function (survey, surveys) {

                var buttons, msg, title, index;

                // index view would contains $scope.surveys but not in detail view
                if (surveys !== undefined) {

                    // IndexOf is only supported as of IE9... so find a work around for older browser.
                    // index = surveys.indexOf(survey);

                    // Fall back method for finding the index of survey
                    angular.forEach(surveys, function (_survey, _index) {
                        if (survey.id == _survey.id) {
                            index = _index;
                            return;
                        }
                    });
                }


                title = 'Confirmation delete';
                msg = 'You are going to delete survey "' + survey.code + '". Are you sure?';
                buttons = [
                    {result: 'cancel', label: 'Cancel'},
                    {result: 'delete', label: 'Delete', cssClass: 'btn-danger'}
                ];
                $dialog.messageBox(title, msg, buttons)
                    .open()
                    .then(function (result) {
                        if (result === 'delete') {

                            survey.$delete({id: survey.id}, function () {

                                // True means we are in index view where $scope.survey was available
                                if (index >= 0) {
                                    surveys.splice(index, 1); // remove from local storage
                                }
                                $location.path('/admin/survey');
                            });
                        }
                    });
            }
        }
    });