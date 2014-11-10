angular.module('myApp').controller('DiscussionMenuCtrl', function($scope, menuData, Restangular, DiscussionModal) {
    'use strict';

    var questionnaireIds = _.pluck(menuData.questionnaires, 'id').join();
    var filterIds = _.pluck(menuData.filters, 'id').join();

    Restangular.all('discussion').getList({fields: 'name,survey,filter', questionnaires: questionnaireIds, filters: filterIds}).then(function(discussions) {

        var onSurveys = [];
        var onQuestionnaires = [];
        var onAnswers = [];

        _.forEach(discussions, function(discussion) {
            if (discussion.survey) {
                onSurveys.push(discussion);
            } else if (discussion.filter) {
                onAnswers.push(discussion);
            } else {
                onQuestionnaires.push(discussion);
            }

        });

        var discussionsAndHeaders = [];
        if (onSurveys.length) {
            discussionsAndHeaders.push({id: -1, name: 'Surveys'});
            discussionsAndHeaders = discussionsAndHeaders.concat(onSurveys);
        }
        if (onQuestionnaires.length) {
            discussionsAndHeaders.push({id: -1, name: 'Questionnaires'});
            discussionsAndHeaders = discussionsAndHeaders.concat(onQuestionnaires);
        }
        if (onAnswers.length) {
            discussionsAndHeaders.push({id: -1, name: 'Answers'});
            discussionsAndHeaders = discussionsAndHeaders.concat(onAnswers);
        }

        $scope.discussions = discussionsAndHeaders;
    });

    $scope.open = DiscussionModal.open;

});
